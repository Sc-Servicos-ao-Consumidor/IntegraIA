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
        Schema::create('ingredient_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');

            $table->boolean('primary_ingredient')->default(true);
            $table->timestamps();

            // Prevent duplicate recipe-ingredient combinations and primary ingredient
            $table->unique(['recipe_id', 'ingredient_id']);

            // Indexes for better performance
            $table->index(['recipe_id', 'primary_ingredient']);
            $table->index(['ingredient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_recipe');
    }
};
