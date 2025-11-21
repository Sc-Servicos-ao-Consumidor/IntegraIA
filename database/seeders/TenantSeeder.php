<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default tenant
        Tenant::firstOrCreate(
            ['slug' => 'unilever'],
            [
                'name' => 'Unilever',
                'domain' => 'unilever.com',
                'status' => true,
            ]
        );
    }
}
