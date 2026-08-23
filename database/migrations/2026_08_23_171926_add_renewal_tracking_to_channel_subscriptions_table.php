<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('renewal_failures')->default(0)->after('last_verified_at');
            $table->timestamp('last_renewal_attempt_at')->nullable()->after('renewal_failures');
            $table->timestamp('last_delivery_at')->nullable()->after('last_renewal_attempt_at');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('channel_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            $table->dropColumn(['renewal_failures', 'last_renewal_attempt_at', 'last_delivery_at']);
        });
    }
};
