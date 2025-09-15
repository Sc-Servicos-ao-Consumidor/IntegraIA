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
        Schema::table('recipes', function (Blueprint $table) {
            // Change JSON fields to string fields
            $table->string('usage_groups')->nullable()->change();
            $table->string('preparation_techniques')->nullable()->change();
            $table->string('consumption_occasion')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            // Revert back to JSON fields
            $table->json('usage_groups')->nullable()->change();
            $table->json('preparation_techniques')->nullable()->change();
            $table->json('consumption_occasion')->nullable()->change();
        });
    }
};