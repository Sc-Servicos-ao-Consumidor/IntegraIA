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
        Schema::table('products', function (Blueprint $table) {
            // Add new fields that should stay in products table
            $table->string('marca')->nullable()->after('group_product_id'); // Brand field from form
            
            // Remove fields that have been moved to product_details table
            $table->dropColumn([
                'url_imagem_principal',  // Images now handled by product_images table
                'descricao_breve',       // Brief description moved to product_details
                'informacao_adicional',  // Additional info moved to product_details
                'ean',                   // EAN moved to product_details
                'qr_code',              // QR code moved to product_details
                'url_rede_social',      // Social media URL moved to product_details
                'quantidade_caixa',     // Box quantity moved to product_details
                'embalagem_tipo',       // Packaging type moved to product_details
                'embalagem_descricao',  // Packaging description moved to product_details
                'peso_liquido',         // Net weight moved to product_details
                'peso_bruto',           // Gross weight moved to product_details
                'validade',             // Shelf life moved to product_details
                'valor',                // Price moved to product_details
                'desconto',             // Discount moved to product_details
                'catalogo',             // Catalog flag moved to product_details
                'lancamento',           // Launch flag moved to product_details
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove added field
            $table->dropColumn('marca');
            
            // Re-add removed fields (in case of rollback)
            $table->text('url_imagem_principal')->nullable();
            $table->string('descricao_breve')->nullable();
            $table->text('informacao_adicional')->nullable();
            $table->string('ean')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('url_rede_social')->nullable();
            $table->string('quantidade_caixa')->nullable();
            $table->string('embalagem_tipo')->nullable();
            $table->string('embalagem_descricao')->nullable();
            $table->decimal('peso_liquido')->nullable();
            $table->decimal('peso_bruto')->nullable();
            $table->string('validade')->nullable();
            $table->decimal('valor', 13, 2)->nullable();
            $table->decimal('desconto')->nullable();
            $table->boolean('catalogo')->default(true);
            $table->boolean('lancamento')->default(false);
        });
    }
};