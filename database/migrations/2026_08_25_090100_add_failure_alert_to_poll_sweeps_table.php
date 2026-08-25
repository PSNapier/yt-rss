<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_sweeps', function (Blueprint $table) {
            // A failure storm that is not a block: the sweep keeps polling, so this is
            // the only durable record that it happened.
            $table->boolean('failure_alert')->default(false)->after('cooldown_triggered');
        });
    }

    public function down(): void
    {
        Schema::table('poll_sweeps', function (Blueprint $table) {
            $table->dropColumn('failure_alert');
        });
    }
};
