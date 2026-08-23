<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channel_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('topic_url');
            $table->string('callback_token', 64)->unique();
            $table->string('secret', 64);
            $table->string('status', 16)->default('pending');
            $table->unsignedInteger('lease_seconds')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_subscriptions');
    }
};
