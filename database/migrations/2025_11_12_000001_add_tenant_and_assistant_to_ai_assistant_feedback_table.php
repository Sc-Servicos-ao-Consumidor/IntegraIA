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
        Schema::table('ai_assistant_feedback', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('assistant_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('ai_assistants')
                ->nullOnDelete();

            $table->index(['tenant_id', 'assistant_id'], 'ai_assistant_feedback_tenant_assistant_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_assistant_feedback', function (Blueprint $table) {
            $table->dropIndex('ai_assistant_feedback_tenant_assistant_idx');
            $table->dropConstrainedForeignId('assistant_id');
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};


