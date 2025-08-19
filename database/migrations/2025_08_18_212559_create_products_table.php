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
            $table->string('slug'); // ok
            $table->string('codigo_padrao')->nullable(); // ok
            $table->string('sku')->nullable(); // ok
            $table->foreignId('group_product_id')->nullable()->constrained('group_products'); // ok

            // caracteristicas do produto
            $table->text('url_imagem_principal')->nullable();
            $table->string('descricao'); // ok
            $table->string('descricao_breve')->nullable(); // ok
            $table->text('informacao_adicional')->nullable(); // ok
            $table->string('ean')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('url_rede_social')->nullable();
            //
            $table->string('quantidade_caixa')->nullable(); // ok
            $table->string('embalagem_tipo')->nullable(); // kg, c, cx, ok
            $table->string('embalagem_descricao')->nullable(); // ok
            $table->decimal('peso_liquido')->nullable(); // ok
            $table->decimal('peso_bruto')->nullable(); // ok
            $table->string('validade')->nullable(); // ok

            $table->decimal('valor', 13, 2)->nullable(); // ok
            $table->decimal('desconto')->nullable();
            //
            $table->boolean('catalogo')->default(true); // ok
            $table->boolean('lancamento')->default(false);

            $table->boolean('status')->default(true);
            $table->timestamps();
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


