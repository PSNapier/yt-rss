<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channel_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'channel_id']);
        });

        // Migrate existing per-group favorites to the new global favorites table.
        // For each pivot row where is_favorite = true, find the group's owner and
        // insert a global favorite record (ignoring duplicates).
        DB::statement("
            INSERT INTO user_channel_favorites (user_id, channel_id, created_at, updated_at)
            SELECT DISTINCT cg.user_id, cgc.channel_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM channel_group_channel cgc
            JOIN channel_groups cg ON cg.id = cgc.channel_group_id
            WHERE cgc.is_favorite = 1
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channel_favorites');
    }
};
