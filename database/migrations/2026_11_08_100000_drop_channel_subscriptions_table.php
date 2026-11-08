<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * WebSub was removed ([030]). The original create and alter migrations stay
     * in place so migrate keeps working against existing databases.
     */
    public function up(): void
    {
        Schema::dropIfExists('channel_subscriptions');
    }

    public function down(): void
    {
        // Intentionally irreversible: the table's history lives in the earlier migrations.
    }
};
