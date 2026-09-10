<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Shorts feed stores the categories a user has *hidden*, not the ones they
     * kept: a group created later is visible without the user touching the filter.
     */
    public function up(): void
    {
        Schema::create('user_hidden_short_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('channel_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'channel_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_hidden_short_groups');
    }
};
