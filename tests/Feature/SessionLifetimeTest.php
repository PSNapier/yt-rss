<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

function sessionRow(string $id, int $lastActivity): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => null,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'pest',
        'payload' => base64_encode(serialize(['marker' => 'alive'])),
        'last_activity' => $lastActivity,
    ]);
}

it('keeps a user authenticated after idling past two hours', function () {
    expect(config('session.lifetime'))->toBe(129600);

    config(['session.driver' => 'database']);
    $handler = Session::driver('database')->getHandler();

    sessionRow('idle-three-hours', now()->subHours(3)->getTimestamp());

    expect($handler->read('idle-three-hours'))->not->toBe('');

    sessionRow('idle-eighty-nine-days', now()->subDays(89)->getTimestamp());

    expect($handler->read('idle-eighty-nine-days'))->not->toBe('');
});

it('expires sessions after ninety days', function () {
    expect(config('session.lifetime'))->toBe(90 * 24 * 60)->toBeInt()->toBeGreaterThan(0)
        ->and(config('session.expire_on_close'))->toBeFalse();

    config(['session.driver' => 'database']);
    $handler = Session::driver('database')->getHandler();

    sessionRow('idle-ninety-one-days', now()->subDays(91)->getTimestamp());

    expect($handler->read('idle-ninety-one-days'))->toBe('');
});

it('sets session lifetime to ninety days in env example', function () {
    $example = file_get_contents(base_path('.env.example'));

    expect($example)->toContain("SESSION_LIFETIME=129600\n");
});
