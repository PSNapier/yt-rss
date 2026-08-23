<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->string('rss_etag')->nullable()->after('last_fetched_at');
            $table->string('rss_last_modified')->nullable()->after('rss_etag');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['rss_etag', 'rss_last_modified']);
        });
    }
};
