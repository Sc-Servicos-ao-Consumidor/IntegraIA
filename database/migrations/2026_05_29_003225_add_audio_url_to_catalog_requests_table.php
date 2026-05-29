<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_requests', function (Blueprint $table) {
            $table->string('audio_url')->nullable()->after('session_id');
            $table->text('question')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('catalog_requests', function (Blueprint $table) {
            $table->dropColumn('audio_url');
            $table->text('question')->nullable(false)->change();
        });
    }
};
