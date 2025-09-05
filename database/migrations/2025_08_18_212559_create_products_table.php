<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 36);
            $table->string('slug');
            $table->string('codigo_padrao')->nullable();
            $table->string('sku')->nullable();
            $table->foreignId('group_product_id')->nullable()->constrained('group_products');
            $table->string('marca')->nullable();
            
            // Core product fields
            $table->string('descricao');
            $table->text('descricao_breve')->nullable();
            $table->text('url_imagem_principal')->nullable();
            $table->string('ean')->nullable();
            
            // Hierarchy/classification ids from API
            $table->unsignedBigInteger('produto_familia_id')->nullable();
            $table->unsignedBigInteger('produto_grupo_id')->nullable();
            $table->unsignedBigInteger('produto_linha_id')->nullable();
            $table->unsignedBigInteger('produto_sub_linha_id')->nullable();
            
            // Product specifications and descriptions
            $table->string('escolha_embalagem')->nullable();
            $table->text('prompt_uso_informacoes_produto')->nullable();
            $table->text('especificacao_produto')->nullable();
            $table->text('perfil_sabor')->nullable();
            $table->text('descricao_tabela_nutricional')->nullable();
            $table->text('descricao_lista_ingredientes')->nullable();
            $table->text('descricao_modos_preparo')->nullable();
            $table->text('descricao_rendimentos')->nullable();
            
            // Location/contact and site
            $table->string('localizacao')->nullable();
            $table->string('nome_responsavel')->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('site')->nullable();
            
            // Status and flags
            $table->boolean('status')->default(true);
            $table->boolean('descontinuado')->default(false);
            $table->vector('embedding', 1536)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['produto_familia_id', 'produto_grupo_id', 'produto_linha_id', 'produto_sub_linha_id'], 'products_produto_hierarchy_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};


