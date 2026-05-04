<?php

use App\Services\YoutubeShortsDetector;
use Illuminate\Support\Facades\Http;

test('returns true when canonical href points at shorts path', function () {
    Http::fake([
        '*youtube.com/watch?v=ssDbeb9vB6g*' => Http::response(
            '<head><link rel="canonical" href="https://www.youtube.com/shorts/ssDbeb9vB6g"></head>',
            200
        ),
    ]);

    expect(app(YoutubeShortsDetector::class)->isShortByWatchPage('ssDbeb9vB6g'))->toBeTrue();
});

test('returns false when canonical href is watch URL', function () {
    Http::fake([
        '*youtube.com/watch?v=1PZKfIyrYOc*' => Http::response(
            '<head><link rel="canonical" href="https://www.youtube.com/watch?v=1PZKfIyrYOc"></head>',
            200
        ),
    ]);

    expect(app(YoutubeShortsDetector::class)->isShortByWatchPage('1PZKfIyrYOc'))->toBeFalse();
});

test('returns null on failed response', function () {
    Http::fake([
        '*youtube.com/*' => Http::response('', 500),
    ]);

    expect(app(YoutubeShortsDetector::class)->isShortByWatchPage('1PZKfIyrYOc'))->toBeNull();
});

test('returns null for invalid video id', function () {
    Http::fake();

    expect(app(YoutubeShortsDetector::class)->isShortByWatchPage('tooshort'))->toBeNull();
    Http::assertNothingSent();
});

test('finds canonical when href precedes rel', function () {
    Http::fake([
        '*youtube.com/watch?v=1PZKfIyrYOc*' => Http::response(
            '<head><link href="https://www.youtube.com/shorts/1PZKfIyrYOc" rel="canonical"></head>',
            200
        ),
    ]);

    expect(app(YoutubeShortsDetector::class)->isShortByWatchPage('1PZKfIyrYOc'))->toBeTrue();
});
