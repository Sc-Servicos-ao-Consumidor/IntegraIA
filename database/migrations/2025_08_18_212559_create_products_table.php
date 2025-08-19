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
            $table->string('marca')->nullable(); // Brand field from form

            // Core product fields that stay in products table
            $table->string('descricao'); // ok
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


