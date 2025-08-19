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
        Schema::create('product_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Additional fields for the relationship
            $table->decimal('quantity', 10, 3)->nullable(); // Quantidade do produto na receita
            $table->string('unit', 50)->nullable(); // Unidade (g, kg, ml, l, unidade, etc.)
            $table->enum('ingredient_type', ['main', 'supporting'])->default('main'); // Tipo de ingrediente
            $table->text('preparation_notes')->nullable(); // Notas específicas de preparo para este produto
            $table->boolean('optional')->default(false); // Se o ingrediente é opcional
            $table->integer('order')->default(0); // Ordem de aparição na receita
            
            $table->timestamps();
            
            // Prevent duplicate recipe-product combinations
            $table->unique(['recipe_id', 'product_id']);
            
            // Indexes for better performance
            $table->index(['recipe_id', 'ingredient_type']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_product');
    }
};
