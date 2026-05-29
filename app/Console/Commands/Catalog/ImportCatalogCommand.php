<?php

namespace App\Console\Commands\Catalog;

use App\Models\CatalogImportRun;
use App\Models\CatalogImportStatus;
use App\Models\Tenant;
use App\Services\Catalog\CatalogImportService;
use Illuminate\Console\Command;

class ImportCatalogCommand extends Command
{
    protected $signature = 'catalog:import
        {file : Path to the XLS file}
        {--tenant= : Tenant ID (required)}';

    protected $description = 'Import catalog products and packages from an XLS file';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if (empty($tenantId)) {
            $this->error('The --tenant option is required.');

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant with ID [{$tenantId}] does not exist.");

            return self::FAILURE;
        }

        $filePath = $this->argument('file');

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $this->error("File [{$filePath}] does not exist or is not readable.");

            return self::FAILURE;
        }

        $run = CatalogImportRun::create([
            'tenant_id' => $tenant->id,
            'catalog_import_status_id' => CatalogImportStatus::firstOrCreate(['name' => 'pending'])->id,
            'file_name' => basename($filePath),
        ]);

        (new CatalogImportService($tenant->id))->import($filePath, $run);

        $run->refresh();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total rows', $run->total_rows],
                ['Created', $run->created_count],
                ['Updated', $run->updated_count],
                ['Skipped', $run->skipped_count],
                ['Failed', $run->failed_count],
            ],
        );

        if ($run->failed_count > 0) {
            $this->warn("Import completed with {$run->failed_count} failed row(s).");

            return self::FAILURE;
        }

        $this->info('Import completed successfully.');

        return self::SUCCESS;
    }
}
