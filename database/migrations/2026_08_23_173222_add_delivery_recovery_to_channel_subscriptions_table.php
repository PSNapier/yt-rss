<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_subscriptions', function (Blueprint $table) {
            /** A push arrived but could not be written: that upload is missing until a poll recovers it. */
            $table->timestamp('delivery_failed_at')->nullable()->after('last_delivery_at');
            /** Set in bulk when the callback was offline; cleared once the channel has been re-polled. */
            $table->timestamp('recovery_due_at')->nullable()->after('delivery_failed_at');
            $table->index('recovery_due_at');
        });
    }

    public function down(): void
    {
        Schema::table('channel_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['recovery_due_at']);
            $table->dropColumn(['delivery_failed_at', 'recovery_due_at']);
        });
    }
};
