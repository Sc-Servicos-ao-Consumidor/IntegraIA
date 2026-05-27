<?php

namespace App\Console\Commands\Catalog;

use App\Jobs\GenerateCatalogProductEmbedding;
use App\Models\CatalogProduct;
use App\Models\Tenant;
use Illuminate\Console\Command;

class EmbedCatalogCommand extends Command
{
    protected $signature = 'catalog:embed
        {--tenant= : Tenant ID (required)}
        {--refresh : Re-embed already-embedded records}';

    protected $description = 'Dispatch embedding jobs for catalog products';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        if (empty($tenantId)) {
            $this->error('The --tenant option is required.');

            return self::FAILURE;
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant with ID [{$tenantId}] does not exist.");

            return self::FAILURE;
        }

        $query = CatalogProduct::where('tenant_id', $tenant->id);

        if ($this->option('refresh')) {
            $query->stale();
        } else {
            $query->withoutEmbedding();
        }

        $count = 0;

        $query->each(function (CatalogProduct $product) use (&$count) {
            GenerateCatalogProductEmbedding::dispatch($product->id);
            $count++;
        });

        $this->info("{$count} record(s) queued for embedding.");

        return self::SUCCESS;
    }
}
