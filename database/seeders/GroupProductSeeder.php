<?php

namespace Database\Seeders;

use App\Models\GroupProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupProductSeeder extends Seeder
{
    public function run(): void
    {
        $groupProducts = [
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'KNR-001',
                'descricao' => 'Knorr Professional - Bases e Molhos',
                'observacao' => 'Produtos Knorr Professional para bases de preparo e molhos prontos',
                'status' => true,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'KNR-002',
                'descricao' => 'Knorr Professional - Caldos e Temperos',
                'observacao' => 'Caldos em pó e temperos da linha Knorr Professional',
                'status' => true,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'KNR-003',
                'descricao' => 'Knorr Professional - Sopas e Cremes',
                'observacao' => 'Sopas desidratadas e cremes instantâneos',
                'status' => true,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'ING-001',
                'descricao' => 'Ingredientes Básicos - Carnes',
                'observacao' => 'Carnes bovinas, suínas, aves e peixes',
                'status' => true,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'ING-002',
                'descricao' => 'Ingredientes Básicos - Vegetais e Ervas',
                'observacao' => 'Vegetais frescos, ervas e temperos naturais',
                'status' => true,
            ],
            [
                'ulid' => (string) Str::ulid(),
                'codigo_padrao' => 'ING-003',
                'descricao' => 'Ingredientes Básicos - Grãos e Cereais',
                'observacao' => 'Arroz, massas, farinhas e cereais diversos',
                'status' => true,
            ],
        ];

        foreach ($groupProducts as $data) {
            GroupProduct::create($data);
        }
    }
}
