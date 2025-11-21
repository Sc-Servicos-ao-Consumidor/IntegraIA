<?php

namespace Database\Seeders;

use App\Models\Cuisine;
use Illuminate\Database\Seeder;

class CuisineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cuisines = [
            'Italiana',
            'Mexicana',
            'Asiática',
            'Brasileira',
            'Francesa',
            'Japonesa',
            'Chinesa',
            'Indiana',
            'Mediterrânea',
            'Árabe',
            'Tailandesa',
            'Coreana',
            'Vietnamita',
            'Espanhola',
            'Portuguesa',
            'Alemã',
            'Americana',
            'Cajun',
            'Tex-Mex',
            'Fusion',
        ];

        foreach ($cuisines as $cuisine) {
            Cuisine::firstOrCreate(['name' => $cuisine]);
        }
    }
}
