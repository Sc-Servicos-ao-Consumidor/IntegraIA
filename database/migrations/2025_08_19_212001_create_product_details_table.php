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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            // Brand information
            $table->string('marca')->nullable(); // Brand
            
            // Packaging information from form
            $table->string('escolha_embalagem')->nullable(); // Selected packaging type
            $table->text('prompt_especificacao_embalagens')->nullable(); // Packaging specification prompt
            $table->text('prompt_uso_informacoes_produto')->nullable(); // Product information usage prompt
            
            // Product specifications and descriptions
            $table->text('especificacao_produto')->nullable(); // Product specification
            $table->text('perfil_sabor')->nullable(); // Flavor profile
            
            // Nutritional information
            $table->text('descricao_tabela_nutricional')->nullable(); // Nutritional table description
            
            // Ingredients information  
            $table->text('descricao_lista_ingredientes')->nullable(); // Ingredients list description
            
            // Preparation methods
            $table->text('descricao_modos_preparo')->nullable(); // Preparation methods description
            
            // Yields/performance information
            $table->text('descricao_rendimentos')->nullable(); // Yields description
            
            // Additional packaging details (moved from main products table)
            $table->string('embalagem_tipo')->nullable(); // kg, c, cx, etc.
            $table->text('embalagem_descricao')->nullable(); // Packaging description
            $table->string('quantidade_caixa')->nullable(); // Box quantity
            
            // Physical properties (moved from main products table)
            $table->decimal('peso_liquido', 8, 2)->nullable(); // Net weight
            $table->decimal('peso_bruto', 8, 2)->nullable(); // Gross weight
            $table->string('validade')->nullable(); // Shelf life
            
            // Pricing (moved from main products table)
            $table->decimal('valor', 13, 2)->nullable(); // Price
            $table->decimal('desconto', 8, 2)->nullable(); // Discount
            
            // Additional product information (moved from main products table)
            $table->text('informacao_adicional')->nullable(); // Additional information
            $table->string('ean')->nullable(); // EAN code
            $table->string('qr_code')->nullable(); // QR code
            $table->string('url_rede_social')->nullable(); // Social media URL
            
            // Product flags (moved from main products table)
            $table->boolean('catalogo')->default(true); // In catalog
            $table->boolean('lancamento')->default(false); // New launch
            $table->boolean('status')->default(true); // Active status
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};