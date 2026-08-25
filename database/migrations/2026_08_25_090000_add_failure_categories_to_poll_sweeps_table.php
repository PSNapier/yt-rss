<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_sweeps', function (Blueprint $table) {
            // A count per coarse transport category, so the shape of a failure storm
            // survives the sweep without a log dive.
            $table->json('failure_categories')->nullable()->after('blocked');
        });
    }

    public function down(): void
    {
        Schema::table('poll_sweeps', function (Blueprint $table) {
            $table->dropColumn('failure_categories');
        });
    }
};
