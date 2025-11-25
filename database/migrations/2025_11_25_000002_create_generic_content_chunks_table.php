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
        Schema::create('generic_content_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generic_content_id')->constrained('generic_contents')->onDelete('cascade');
            $table->string('chunk_type');
            $table->text('content');
            $table->unsignedSmallInteger('position')->default(0);
            $table->vector('embedding', 3072)->nullable();
            $table->timestamps();

            $table->index(['generic_content_id', 'chunk_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_content_chunks');
    }
};
