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
            // Hierarchy/classification ids from API
            $table->unsignedBigInteger('produto_familia_id')->nullable()->after('group_product_id');
            $table->unsignedBigInteger('produto_grupo_id')->nullable()->after('produto_familia_id');
            $table->unsignedBigInteger('produto_linha_id')->nullable()->after('produto_grupo_id');
            $table->unsignedBigInteger('produto_sub_linha_id')->nullable()->after('produto_linha_id');

            // Main/media URLs and descriptors
            $table->text('url_imagem_principal')->nullable()->after('sku');
            $table->text('descricao_breve')->nullable()->after('descricao');
            $table->text('informacao_adicional')->nullable()->after('descricao_breve');

            // Identifiers and links
            $table->string('ean')->nullable()->after('codigo_padrao');

            // Packaging/basic properties
            $table->string('quantidade_caixa')->nullable()->after('ean');
            $table->string('embalagem_tipo')->nullable()->after('quantidade_caixa');
            $table->text('embalagem_descricao')->nullable()->after('embalagem_tipo');

            // Location/contact and site
            $table->string('localizacao')->nullable()->after('embalagem_descricao');
            $table->string('nome_responsavel')->nullable()->after('localizacao');
            $table->string('telefone')->nullable()->after('nome_responsavel');
            $table->string('whatsapp')->nullable()->after('telefone');
            $table->string('site')->nullable()->after('whatsapp');

            // Physical properties
            $table->decimal('peso_liquido', 8, 2)->nullable()->after('site');
            $table->decimal('peso_bruto', 8, 2)->nullable()->after('peso_liquido');
            $table->date('validade')->nullable()->after('peso_bruto');

            // Pricing
            $table->decimal('valor', 13, 2)->nullable()->after('validade');

            // Flags
            $table->boolean('catalogo')->default(false)->after('valor');
            $table->boolean('descontinuado')->default(false)->after('catalogo');
            $table->boolean('lancamento')->default(false)->after('descontinuado');

            // Useful indexes
            $table->index(['produto_familia_id', 'produto_grupo_id', 'produto_linha_id', 'produto_sub_linha_id'], 'products_produto_hierarchy_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_produto_hierarchy_idx');

            $table->dropColumn([
                'produto_familia_id',
                'produto_grupo_id',
                'produto_linha_id',
                'produto_sub_linha_id',
                'url_imagem_principal',
                'descricao_breve',
                'informacao_adicional',
                'ean',
                'quantidade_caixa',
                'embalagem_tipo',
                'embalagem_descricao',
                'localizacao',
                'nome_responsavel',
                'telefone',
                'whatsapp',
                'site',
                'peso_liquido',
                'peso_bruto',
                'validade',
                'valor',
                'catalogo',
                'descontinuado',
                'lancamento',
            ]);
        });
    }
};


