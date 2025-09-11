<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProductImportService;
use App\Models\Tenant;

class ImportProducts extends Command
{
    protected $signature = 'products:import {--url=} {--token=}';

    protected $description = 'Import products from external paginated API';

    public function handle(ProductImportService $service): int
    {
        $url = $this->option('url') ?? config('services.products_api.url');
        if (!$url) {
            $this->error('Base URL is required. Pass --url or set services.products_api.url');
            return self::FAILURE;
        }

        $headers = [];
        $token = $this->option('token') ?? config('services.products_api.token');

        foreach (Tenant::active() as $tenant) {

            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
                    $headers['tenant'] = $tenant->id;
            }
            $this->info('Starting product import for tenant: ' . $tenant->name);
            $service->importAll($url, $headers);
        }

        $this->info('Product import completed.');
        return self::SUCCESS;
    }
}
