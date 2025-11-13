<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('ai_assistant_feedback', 'aassistant_logs');
    }

    public function down(): void
    {
        Schema::rename('aassistant_logs', 'ai_assistant_feedback');
    }
};


