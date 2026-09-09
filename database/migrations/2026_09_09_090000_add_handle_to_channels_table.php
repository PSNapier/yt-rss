<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            // Normalised YouTube @handle (lowercased, leading @ kept). Caches the
            // handle -> channel_id resolution so a repeat add costs no API unit.
            $table->string('handle')->nullable()->index()->after('channel_id');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropIndex(['handle']);
            $table->dropColumn('handle');
        });
    }
};
