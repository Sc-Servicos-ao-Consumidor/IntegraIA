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
        Schema::create('generic_contents', function (Blueprint $table) {
            $table->id();

            // Multi-tenant ownership
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Generic content identity
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('type')->nullable(); // e.g. page, post, block, note

            // Flexible payloads
            $table->text('content')->nullable(); // free-form text/markdown/html
            $table->json('data')->nullable();    // arbitrary structured content
            $table->json('meta')->nullable();    // SEO, settings, etc.

            $table->timestamps();

            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generic_contents');
    }
};
