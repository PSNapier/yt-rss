<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_api_usage', function (Blueprint $table) {
            $table->id();
            // Google's quota resets at midnight America/Los_Angeles, so the day is
            // keyed by the Pacific date rather than the app timezone.
            $table->string('date_pt', 10)->unique();
            $table->unsignedInteger('units_used')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_api_usage');
    }
};
