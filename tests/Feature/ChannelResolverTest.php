<?php

use App\Services\ChannelResolver;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(fn () => Config::set('services.youtube.api_key', null));

test('fromChannelId rejects invalid IDs', function () {
    expect(fn () => (new ChannelResolver)->fromChannelId('not-valid'))
        ->toThrow(InvalidArgumentException::class);
});

test('fromChannelId accepts valid UC ID without API key', function () {
    $info = (new ChannelResolver(apiKey: null))->fromChannelId('UC'.str_repeat('A', 22));

    expect($info['channel_id'])->toBe('UC'.str_repeat('A', 22));
    expect($info['rss_url'])->toContain('feeds/videos.xml?channel_id=UC');
});

test('fromHandle requires API key', function () {
    expect(fn () => (new ChannelResolver(apiKey: null))->fromHandle('@mkbhd'))
        ->toThrow(RuntimeException::class);
});

test('fromHandle resolves via YouTube API', function () {
    Http::fake([
        'googleapis.com/*' => Http::response([
            'items' => [
                ['id' => 'UCBJycsmduvYEL83R_U4JriQ', 'snippet' => ['title' => 'MKBHD']],
            ],
        ]),
    ]);

    $info = (new ChannelResolver(apiKey: 'fake-key'))->fromHandle('@mkbhd');

    expect($info['channel_id'])->toBe('UCBJycsmduvYEL83R_U4JriQ');
    expect($info['name'])->toBe('MKBHD');
});

test('fromHandle throws when API returns no items', function () {
    Http::fake(['googleapis.com/*' => Http::response(['items' => []])]);

    expect(fn () => (new ChannelResolver(apiKey: 'fake-key'))->fromHandle('@nonexistent'))
        ->toThrow(RuntimeException::class);
});
