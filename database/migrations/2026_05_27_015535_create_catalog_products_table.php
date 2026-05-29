<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->string('codigo_padrao');
            $table->string('sku')->nullable();
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->string('product_img_url')->nullable();
            $table->string('category_name')->nullable();
            $table->string('sub_category_name')->nullable();
            $table->string('line_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->text('searchable_text')->nullable();
            $table->vector('embedding', 3072)->nullable();
            $table->timestamp('embedded_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'codigo_padrao'], 'catalog_products_tenant_codigo_unique');
            $table->index('tenant_id', 'catalog_products_tenant_id_idx');
            $table->index('embedded_at', 'catalog_products_embedded_at_idx');
            $table->index('category_name', 'catalog_products_category_name_idx');
            $table->index('brand_name', 'catalog_products_brand_name_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
