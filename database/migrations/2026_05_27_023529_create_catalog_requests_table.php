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
        Schema::create('catalog_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants');
            $table->foreignId('catalog_pipeline_status_id')->constrained('catalog_pipeline_statuses');
            $table->string('contact_id');
            $table->text('question');
            $table->json('search_results')->nullable();
            $table->json('ai_answer')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id', 'catalog_requests_tenant_id_idx');
            $table->index(['tenant_id', 'catalog_pipeline_status_id'], 'catalog_requests_tenant_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_requests');
    }
};
