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
        Schema::create('content_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('chunk_type');
            $table->text('content');
            $table->unsignedSmallInteger('position')->default(0);
            $table->vector('embedding', 3072)->nullable();
            $table->timestamps();

            $table->index(['content_id', 'chunk_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_chunks');
    }
};
