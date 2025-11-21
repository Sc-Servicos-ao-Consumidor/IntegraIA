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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // Image type categorization based on form sections
            $table->enum('image_type', [
                'principal',           // Main product image (url_imagem_principal)
                'embalagem',          // Packaging images (Embalagens Cadastradas)
                'tabela_nutricional', // Nutritional table image (Imagem Tabela Nutricional)
                'lista_ingredientes', // Ingredients list image (Imagem Lista de Ingredientes)
                'modos_preparo',      // Preparation methods image (Imagem dos Modos de Preparo)
                'rendimentos',        // Yields image (Imagem dos Rendimentos)
                'outras',              // Other images
            ]);

            // Image information
            $table->string('nome')->nullable(); // Image name/title
            $table->text('url'); // Image URL
            $table->text('descricao')->nullable(); // Image description
            $table->string('alt_text')->nullable(); // Alternative text for accessibility

            // Display order for multiple images of same type
            $table->integer('ordem')->default(0); // Display order

            // Image properties
            $table->string('mime_type')->nullable(); // Image MIME type
            $table->integer('tamanho')->nullable(); // File size in bytes
            $table->integer('largura')->nullable(); // Image width
            $table->integer('altura')->nullable(); // Image height

            // Status and visibility
            $table->boolean('ativo')->default(true); // Active status
            $table->boolean('valida_produtos_embalagem')->default(false); // Valid for packaged products (from form)

            $table->timestamps();

            // Add indexes for better performance
            $table->index(['product_id', 'image_type']);
            $table->index(['product_id', 'image_type', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
