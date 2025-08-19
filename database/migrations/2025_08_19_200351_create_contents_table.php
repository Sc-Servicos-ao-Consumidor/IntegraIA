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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            
            // Basic content information
            $table->string('nome_conteudo')->nullable();
            $table->string('content_code')->unique()->nullable();
            
            // Content descriptions
            $table->text('descricao_tabela_nutricional')->nullable();
            $table->text('descricao_lista_ingredientes')->nullable();
            $table->text('descricao_modos_preparo')->nullable();
            $table->text('descricao_rendimentos')->nullable();
            
            // Image URLs for each section
            $table->string('imagem_tabela_nutricional')->nullable();
            $table->string('imagem_lista_ingredientes')->nullable();
            $table->string('imagem_modos_preparo')->nullable();
            $table->string('imagem_rendimentos')->nullable();
            
            // Additional image arrays (stored as JSON)
            $table->json('imagens_nutricionais_cadastradas')->nullable();
            $table->json('imagens_ingredientes_cadastradas')->nullable();
            $table->json('imagens_preparo_cadastradas')->nullable();
            $table->json('imagens_rendimentos_cadastradas')->nullable();
            
            // Content type and classification
            $table->string('tipo_conteudo')->nullable();
            $table->string('pilares')->nullable(); // Based on dropdown in image
            $table->string('canal')->nullable();
            
            // Links and references
            $table->json('links_conteudo')->nullable();
            
            // Status and metadata
            $table->boolean('cozinheiro')->default(false);
            $table->boolean('comprador')->default(false);
            $table->boolean('administrador')->default(false);
            $table->boolean('status')->default(true);
            
            // SEO and prompt content
            $table->text('prompt_conteudo')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
