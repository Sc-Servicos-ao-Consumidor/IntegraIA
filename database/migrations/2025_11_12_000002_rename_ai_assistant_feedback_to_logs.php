<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('ai_assistant_feedback', 'assistant_logs');
    }

    public function down(): void
    {
        Schema::rename('assistant_logs', 'ai_assistant_feedback');
    }
};
