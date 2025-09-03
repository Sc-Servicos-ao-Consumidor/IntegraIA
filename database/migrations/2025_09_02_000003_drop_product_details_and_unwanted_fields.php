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
        // Drop the product_details table entirely
        Schema::dropIfExists('product_details');

        // Drop unwanted columns from products
        Schema::table('products', function (Blueprint $table) {
            $columnsToDrop = [
                'valor',            // pricing
                'catalogo',         // flag
                'lancamento',       // flag
                'qr_code',          // qr code
                'url_rede_social',  // social url
                'marca',            // brand
            ];

            // Some columns may not exist depending on migration order; suppress errors per DB driver
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate dropped columns (types must match previous definitions)
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'marca')) {
                $table->string('marca')->nullable();
            }
            if (!Schema::hasColumn('products', 'valor')) {
                $table->decimal('valor', 13, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'catalogo')) {
                $table->boolean('catalogo')->default(false);
            }
            if (!Schema::hasColumn('products', 'lancamento')) {
                $table->boolean('lancamento')->default(false);
            }
            if (!Schema::hasColumn('products', 'qr_code')) {
                $table->string('qr_code')->nullable();
            }
            if (!Schema::hasColumn('products', 'url_rede_social')) {
                $table->string('url_rede_social')->nullable();
            }
        });

        // We intentionally do not recreate product_details table in down() to avoid data ambiguity
    }
};


