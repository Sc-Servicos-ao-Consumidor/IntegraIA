<?php

namespace App\Services\Catalog;

class CatalogFieldNormalizer
{
    private const DESCRIPTION_FIELDS = [
        'product_description',
        'package_description',
    ];

    private const FLOAT_FIELDS = [
        'gross_weight',
        'net_weight',
    ];

    public function normalize(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $normalized[$key] = $this->normalizeField((string) $key, $value);
        }

        return $normalized;
    }

    private function normalizeField(string $key, mixed $value): mixed
    {
        if (in_array($key, self::FLOAT_FIELDS, true)) {
            return $this->toFloatOrNull($value);
        }

        if (! is_string($value)) {
            return $value;
        }

        if (in_array($key, self::DESCRIPTION_FIELDS, true)) {
            $trimmed = trim($value, " \t\0\x0B");

            return $trimmed === '' ? null : $trimmed;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function toFloatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = str_replace(',', '.', (string) $value);

        return is_numeric($cleaned) ? (float) $cleaned : null;
    }
}
