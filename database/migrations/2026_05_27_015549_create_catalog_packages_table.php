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
        Schema::create('catalog_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_product_id')->constrained('catalog_products');
            $table->string('sku_package');
            $table->string('sku_package_name')->nullable();
            $table->text('package_description')->nullable();
            $table->decimal('gross_weight', 10, 4)->nullable();
            $table->decimal('net_weight', 10, 4)->nullable();
            $table->string('ean')->nullable();
            $table->string('package_img_url')->nullable();
            $table->timestamps();

            $table->unique(['catalog_product_id', 'sku_package'], 'catalog_packages_product_sku_unique');
            $table->index('catalog_product_id', 'catalog_packages_product_id_idx');
            $table->index('ean', 'catalog_packages_ean_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_packages');
    }
};
