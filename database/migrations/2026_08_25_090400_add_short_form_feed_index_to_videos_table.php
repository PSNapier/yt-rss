<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Both feed reads filter is_short and order by published_at within a set of
            // channels. Shorts are kept forever now, so without this the filter is a
            // row-by-row reject over a set that only grows.
            $table->index(['channel_id', 'is_short', 'published_at'], 'videos_channel_short_published_index');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_channel_short_published_index');
        });
    }
};
