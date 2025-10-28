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
        if (Schema::hasTable('user_tenants') && Schema::hasColumn('user_tenants', 'tenant')) {
            // drop the column tenant
            Schema::table('user_tenants', function (Blueprint $table) {
                $table->dropColumn('tenant');
            });

            if (Schema::hasTable('user_tenants') && Schema::hasColumn('user_tenants', 'tenant_id')) {
                Schema::table('user_tenants', function (Blueprint $table) {
                    $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete()->cascadeOnUpdate();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_tenants') && Schema::hasColumn('user_tenants', 'tenant')) {
            Schema::table('user_tenants', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};


