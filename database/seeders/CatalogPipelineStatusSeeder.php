<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogPipelineStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['pending', 'processing', 'completed', 'failed'];

        foreach ($statuses as $status) {
            DB::table('catalog_pipeline_statuses')->insertOrIgnore([
                'name' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
