<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assistant_logs') && Schema::hasColumn('assistant_logs', 'expected_response') && ! Schema::hasColumn('assistant_logs', 'comment')) {
            DB::statement('ALTER TABLE assistant_logs RENAME COLUMN expected_response TO comment');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assistant_logs') && Schema::hasColumn('assistant_logs', 'comment') && ! Schema::hasColumn('assistant_logs', 'expected_response')) {
            DB::statement('ALTER TABLE assistant_logs RENAME COLUMN comment TO expected_response');
        }
    }
};
