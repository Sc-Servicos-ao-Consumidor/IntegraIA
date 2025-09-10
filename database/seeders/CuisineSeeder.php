<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cuisine;

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
            'Fusion'
        ];

        foreach ($cuisines as $cuisine) {
            Cuisine::firstOrCreate(['name' => $cuisine]);
        }
    }
}
