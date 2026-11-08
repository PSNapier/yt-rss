<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('feed_show_watched')->default(true)->after('feed_cap_enabled');
            $table->boolean('feed_starred_only')->default(false)->after('feed_show_watched');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['feed_show_watched', 'feed_starred_only']);
        });
    }
};
