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
        Schema::create('ai_assistant_feedback', function (Blueprint $table) {
            $table->id();

            // Optional session or request correlation
            $table->string('session_id')->nullable()->index();

            // Payload
            $table->text('query');
            $table->longText('response')->nullable();
            $table->string('rating')->nullable()->index(); // 'up' | 'down' can be changed
            $table->text('expected_response')->nullable();

            // For extensibility
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_assistant_feedback');
    }
};
