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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('raw_text');
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->vector('embedding', 1536)->nullable();
            
            // Codigo Receita
            $table->string('recipe_code')->nullable();
            
            // Nome Receita
            $table->string('recipe_name')->nullable();
            
            // Culinária
            $table->string('cuisine')->nullable();
            
            // Tipo Receita
            $table->string('recipe_type')->nullable();
            
            // Ordem Serviço
            $table->string('service_order')->nullable();
            
            // Tempo Preparo (in minutes)
            $table->integer('preparation_time')->nullable();
            
            // Grau Dificuldade
            $table->string('difficulty_level')->nullable();
            
            // Rendimento
            $table->string('yield')->nullable();
            
            // Canal
            $table->string('channel')->nullable();
            
            // Descrição da Receita
            $table->text('recipe_description')->nullable();
            
            // Descrição dos Ingredientes
            $table->text('ingredients_description')->nullable();
            
            // Modo de Preparo
            $table->text('preparation_method')->nullable();
            
            // Ingredientes Principais (stored as JSON for flexibility)
            $table->json('main_ingredients')->nullable();
            
            // Ingredientes Apoio (stored as JSON for flexibility)
            $table->json('supporting_ingredients')->nullable();
            
            // Grupos de Uso (stored as JSON for flexibility)
            $table->json('usage_groups')->nullable();
            
            // Técnicas de Preparo (stored as JSON for flexibility)
            $table->json('preparation_techniques')->nullable();
            
            // Ocasião de Consumo (stored as JSON for flexibility)
            $table->json('consumption_occasion')->nullable();
            
            // Link Imagens em Geral
            $table->string('general_images_link')->nullable();
            
            // Codigo Produto
            $table->string('product_code')->nullable();
            
            // Codigo Conteudo
            $table->string('content_code')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
