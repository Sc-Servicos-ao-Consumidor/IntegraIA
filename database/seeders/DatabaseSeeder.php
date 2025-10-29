<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123123123'
        ]);

        $this->call([
            TenantSeeder::class,
            GroupProductSeeder::class,
            ProductSeeder::class,
            IngredientSeeder::class,
            RecipeSeeder::class,
            ContentSeeder::class,
            CuisineSeeder::class,
        ]);

        // Attach default tenant to the default user
        $tenantId = Tenant::where('slug', 'unilever')->value('id');
        if ($tenantId) {
            $user->tenants()->syncWithoutDetaching([$tenantId]);
        }
    }
}
