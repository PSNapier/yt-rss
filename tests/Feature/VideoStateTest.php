<?php

use App\Models\User;
use App\Models\UserVideoState;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can mark a video watched', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('videos.state.store', 'abc123XYZ_-'), ['state' => 'watched'])
        ->assertRedirect();

    $this->assertDatabaseHas('user_video_states', [
        'user_id' => $user->id,
        'youtube_video_id' => 'abc123XYZ_-',
        'state' => 'watched',
    ]);
});

test('user can hide a video', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('videos.state.store', 'vid000'), ['state' => 'hidden']);

    $this->assertDatabaseHas('user_video_states', [
        'user_id' => $user->id,
        'youtube_video_id' => 'vid000',
        'state' => 'hidden',
    ]);
});

test('user can unmark watched (delete state) by sending null', function () {
    $user = User::factory()->create();
    UserVideoState::factory()->watched()->create([
        'user_id' => $user->id,
        'youtube_video_id' => 'vid000',
    ]);

    $this->actingAs($user)
        ->post(route('videos.state.store', 'vid000'), ['state' => null]);

    $this->assertDatabaseMissing('user_video_states', [
        'user_id' => $user->id,
        'youtube_video_id' => 'vid000',
    ]);
});

test('state transitions update existing row (no duplicates)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('videos.state.store', 'vid000'), ['state' => 'watched']);
    $this->actingAs($user)->post(route('videos.state.store', 'vid000'), ['state' => 'hidden']);

    expect(UserVideoState::where('user_id', $user->id)->count())->toBe(1);
    expect(UserVideoState::where('user_id', $user->id)->first()->state)->toBe('hidden');
});

test('state writes are scoped to authenticated user (cannot affect others)', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $this->actingAs($alice)
        ->post(route('videos.state.store', 'vid000'), ['state' => 'watched']);

    $this->assertDatabaseHas('user_video_states', [
        'user_id' => $alice->id,
        'youtube_video_id' => 'vid000',
    ]);
    $this->assertDatabaseMissing('user_video_states', [
        'user_id' => $bob->id,
    ]);
});
