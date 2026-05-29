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
        Schema::create('catalog_import_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_import_run_id')->constrained('catalog_import_runs');
            $table->integer('row_number')->nullable();
            $table->json('row_data')->nullable();
            $table->text('error_message');
            $table->timestamps();

            $table->index('catalog_import_run_id', 'catalog_import_errors_run_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_import_errors');
    }
};
