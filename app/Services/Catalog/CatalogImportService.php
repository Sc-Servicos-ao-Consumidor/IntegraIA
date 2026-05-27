<?php

namespace App\Services\Catalog;

use App\Imports\CatalogImport;
use App\Models\CatalogImportError;
use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\CatalogPackage;
use App\Models\CatalogProduct;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class CatalogImportService
{
    public function __construct(
        private readonly int $tenantId,
        private readonly CatalogFieldNormalizer $normalizer = new CatalogFieldNormalizer,
    ) {}

    public function import(UploadedFile|string $file, CatalogImportRun $run): void
    {
        $run->update([
            'started_at' => now(),
            'catalog_import_status_id' => CatalogImportStatus::firstOrCreate(['name' => 'running'])->id,
        ]);

        try {
            $import = new CatalogImport($this->normalizer);
            Excel::import($import, $file);

            $rows = $import->getRows();
            $skippedCount = $import->getSkippedCount();

            $totalRows = $rows->count() + $skippedCount;
            $createdCount = 0;
            $updatedCount = 0;
            $failedCount = 0;

            $grouped = $rows->groupBy('product_id');

            foreach ($grouped as $productId => $productRows) {
                try {
                    $firstRow = $productRows->first();

                    $productData = [
                        'product_name' => $firstRow['product_name'] ?? null,
                        'product_description' => $firstRow['product_description'] ?? null,
                        'product_img_url' => $firstRow['product_img_url'] ?? null,
                        'category_name' => $firstRow['category_name'] ?? null,
                        'sub_category_name' => $firstRow['sub_category_name'] ?? null,
                        'line_name' => $firstRow['line_name'] ?? null,
                        'brand_name' => $firstRow['brand_name'] ?? null,
                    ];

                    $existing = CatalogProduct::withoutGlobalScopes()
                        ->where('tenant_id', $this->tenantId)
                        ->where('codigo_padrao', $productId)
                        ->first();

                    if ($existing) {
                        $existing->update($productData);
                        $product = $existing;
                        $updatedCount++;
                    } else {
                        $product = CatalogProduct::create(array_merge($productData, [
                            'tenant_id' => $this->tenantId,
                            'codigo_padrao' => $productId,
                        ]));
                        $createdCount++;
                    }

                    foreach ($productRows as $row) {
                        try {
                            $skuPackage = $row['sku_package'];

                            $packageData = [
                                'sku_package_name' => $row['sku_package_name'] ?? null,
                                'package_description' => $row['package_description'] ?? null,
                                'gross_weight' => $row['gross_weight'] ?? null,
                                'net_weight' => $row['net_weight'] ?? null,
                                'ean' => $row['ean'] ?? null,
                                'package_img_url' => $row['package_img_url'] ?? null,
                            ];

                            CatalogPackage::withoutGlobalScopes()
                                ->updateOrCreate(
                                    [
                                        'catalog_product_id' => $product->id,
                                        'sku_package' => $skuPackage,
                                    ],
                                    $packageData,
                                );
                        } catch (Throwable $e) {
                            $failedCount++;
                            CatalogImportError::create([
                                'catalog_import_run_id' => $run->id,
                                'row_number' => $row['_row_number'] ?? null,
                                'row_data' => collect($row)->except('_row_number')->all(),
                                'error_message' => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (Throwable $e) {
                    $failedCount += $productRows->count();
                    foreach ($productRows as $row) {
                        CatalogImportError::create([
                            'catalog_import_run_id' => $run->id,
                            'row_number' => $row['_row_number'] ?? null,
                            'row_data' => collect($row)->except('_row_number')->all(),
                            'error_message' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $statusName = $failedCount > 0 && $createdCount === 0 && $updatedCount === 0
                ? 'failed'
                : 'completed';

            $run->update([
                'catalog_import_status_id' => CatalogImportStatus::firstOrCreate(['name' => $statusName])->id,
                'total_rows' => $totalRows,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $run->update([
                'catalog_import_status_id' => CatalogImportStatus::firstOrCreate(['name' => 'failed'])->id,
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
