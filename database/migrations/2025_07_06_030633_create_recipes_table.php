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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();

            // Recipe identification
            $table->string('recipe_code')->nullable();
            $table->string('recipe_name')->nullable();

            // Recipe classification
            $table->string('recipe_type')->nullable();
            $table->string('service_order')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->string('channel')->nullable();

            // Recipe details
            $table->integer('preparation_time')->nullable();
            $table->string('yield')->nullable();
            $table->text('recipe_description')->nullable();
            $table->text('ingredients_description')->nullable();
            $table->text('preparation_method')->nullable();

            // JSON fields for flexible data
            $table->json('usage_groups')->nullable();
            $table->json('preparation_techniques')->nullable();
            $table->json('consumption_occasion')->nullable();

            $table->vector('embedding', 1536)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
