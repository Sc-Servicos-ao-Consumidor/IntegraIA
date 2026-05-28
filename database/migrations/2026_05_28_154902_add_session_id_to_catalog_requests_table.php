<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_requests', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('contact_id');
            $table->index('session_id', 'catalog_requests_session_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_requests', function (Blueprint $table) {
            $table->dropIndex('catalog_requests_session_id_idx');
            $table->dropColumn('session_id');
        });
    }
};
