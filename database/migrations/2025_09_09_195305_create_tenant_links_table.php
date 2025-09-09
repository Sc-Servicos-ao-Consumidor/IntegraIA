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
        Schema::create('tenant_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete()->cascadeOnUpdate();

            // Identifier in the external system (string/uuid)
            $table->string('external_tenant_id');

            // Optional convenience fields
            $table->string('external_slug')->nullable();
            $table->string('external_domain')->nullable();

            $table->timestamps();

            // Constraints / indexes (single external system)
            $table->unique(['tenant_id']);
            $table->unique(['external_tenant_id']);
            $table->index(['tenant_id']);
            $table->index(['external_tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_links');
    }
};
