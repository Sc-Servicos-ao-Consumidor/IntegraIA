<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogImportStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'running', 'completed', 'failed'];

        foreach ($statuses as $status) {
            DB::table('catalog_import_statuses')->insertOrIgnore([
                'name' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
