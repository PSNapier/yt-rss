<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_sweeps', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at')->index();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('channels_polled')->default(0);
            $table->unsignedInteger('fetched')->default(0);
            $table->unsignedInteger('not_modified')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('blocked')->default(0);
            $table->boolean('cap_hit')->default(false);
            $table->boolean('cooldown_triggered')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_sweeps');
    }
};
