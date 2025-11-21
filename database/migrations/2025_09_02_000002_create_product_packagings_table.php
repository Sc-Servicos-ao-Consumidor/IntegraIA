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
        Schema::create('product_packagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->char('ulid', 36)->nullable();
            $table->string('codigo_padrao')->nullable();
            $table->string('sku')->nullable();
            $table->string('ean')->nullable();
            $table->string('descricao')->nullable();
            $table->text('prompt_especificacao_embalagens')->nullable();
            $table->string('quantidade_caixa')->nullable();
            $table->string('embalagem_tipo')->nullable();
            $table->decimal('peso_liquido', 8, 2)->nullable();
            $table->decimal('peso_bruto', 8, 2)->nullable();
            $table->date('validade')->nullable();
            $table->boolean('descontinuado')->default(false);
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_packagings');
    }
};
