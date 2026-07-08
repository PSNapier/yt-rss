<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_channel_caps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained()->cascadeOnDelete();
            // Max unwatched videos shown for this channel when cap mode is on.
            // 0 means unlimited. A missing row means the default cap applies.
            $table->unsignedInteger('cap');
            $table->timestamps();

            $table->unique(['user_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_channel_caps');
    }
};
