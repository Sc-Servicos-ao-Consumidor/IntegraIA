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
        Schema::create('product_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('chunk_type');
            $table->text('content');
            $table->unsignedSmallInteger('position')->default(0);
            $table->vector('embedding', 3072)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'chunk_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_chunks');
    }
};


