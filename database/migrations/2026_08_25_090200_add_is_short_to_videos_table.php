<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Short-form entries are filtered at read time rather than deleted, so a
            // misclassification costs a hidden row instead of the user's watched state.
            $table->boolean('is_short')->default(false)->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('is_short');
        });
    }
};
