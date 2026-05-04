<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_video_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('youtube_video_id');
            $table->enum('state', ['watched', 'hidden']);
            $table->timestamps();

            $table->unique(['user_id', 'youtube_video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_video_states');
    }
};
