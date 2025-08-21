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
            $table->dropColumn([
                'title',
                'raw_text', 
                'metadata',
                'tags',
                'top_dish'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->text('raw_text')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->string('top_dish')->nullable();
        });
    }
};
