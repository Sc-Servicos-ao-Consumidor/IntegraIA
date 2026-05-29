<?php

namespace App\Imports;

use App\Services\Catalog\CatalogFieldNormalizer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class CatalogImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithHeadingRow
{
    private Collection $rows;

    private array $errors = [];

    private array $failures = [];

    private int $skippedCount = 0;

    public function __construct(private readonly CatalogFieldNormalizer $normalizer)
    {
        $this->rows = collect();
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $row) {
            $normalized = $this->normalizer->normalize($row->toArray());

            if (empty($normalized['product_id']) || empty($normalized['sku_package'])) {
                $this->skippedCount++;

                continue;
            }

            $normalized['_row_number'] = (int) $index + 2; // row 1 is the header
            $this->rows->push($normalized);
        }
    }

    public function onError(Throwable $e): void
    {
        $this->errors[] = $e;
    }

    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
        }
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
