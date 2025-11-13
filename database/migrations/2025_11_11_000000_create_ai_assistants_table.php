<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assistants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->text('system_prompt'); // main prompt for the assistant
            $table->boolean('default')->default(false);
            $table->string('model')->nullable(); // e.g., gpt-4o, etc.
            $table->json('tools')->nullable(); // optional list of tool names/configs
            $table->boolean('status')->default(true); // active/inactive
            $table->timestamps();

            $table->index(['tenant_id', 'default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistants');
    }
};
