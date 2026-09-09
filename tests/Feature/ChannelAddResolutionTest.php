<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use App\Models\YoutubeApiUsage;
use App\Services\YoutubeApiClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('services.youtube.api_key', 'fake-key');
});

const RESOLVED_ID = 'UCBJycsmduvYEL83R_U4JriQ';

function atomFeed(string $title): string
{
    return '<?xml version="1.0"?><feed xmlns="http://www.w3.org/2005/Atom"><title>'
        .$title.'</title></feed>';
}

/** Fake every outbound call: RSS returns a title, the Data API returns one channel. */
function fakeYoutube(?array $items = null): void
{
    $items ??= [[
        'id' => RESOLVED_ID,
        'snippet' => ['title' => 'MKBHD', 'customUrl' => '@mkbhd'],
    ]];

    Http::fake([
        'www.youtube.com/feeds/*' => Http::response(atomFeed('Feed Title')),
        'googleapis.com/*' => Http::response(['items' => $items]),
        '*' => Http::response('', 200),
    ]);
}

/** @return array{0: User, 1: ChannelGroup} */
function subscriber(): array
{
    $user = User::factory()->create();

    return [$user, ChannelGroup::factory()->for($user)->create()];
}

function addChannel(User $user, ChannelGroup $group, string $value)
{
    return test()->actingAs($user)->post('/subscriptions', [
        'mode' => 'auto',
        'value' => $value,
        'group_ids' => [$group->id],
    ]);
}

function unitsSpent(): int
{
    return (int) YoutubeApiUsage::query()->sum('units_used');
}

function apiCallCount(): int
{
    $count = 0;
    Http::recorded(function ($request) use (&$count) {
        if (str_contains($request->url(), 'googleapis.com')) {
            $count++;
        }
    });

    return $count;
}

test('a raw UC id or a /channel/ URL adds the channel with no API unit and no tick', function (string $value) {
    fakeYoutube();
    [$user, $group] = subscriber();

    addChannel($user, $group, $value)->assertSessionHasNoErrors();

    expect(Channel::where('channel_id', RESOLVED_ID)->exists())->toBeTrue();
    expect(apiCallCount())->toBe(0);
    expect(unitsSpent())->toBe(0);
})->with([
    'raw id' => RESOLVED_ID,
    'channel URL' => 'https://www.youtube.com/channel/'.RESOLVED_ID,
]);

test('a handle, /@handle URL, or /user/ URL resolves via the API for exactly one unit and stores the handle', function (string $value) {
    fakeYoutube();
    [$user, $group] = subscriber();

    addChannel($user, $group, $value)->assertSessionHasNoErrors();

    $channel = Channel::where('channel_id', RESOLVED_ID)->first();

    expect($channel)->not->toBeNull();
    expect($channel->handle)->toBe('@mkbhd');
    expect(apiCallCount())->toBe(1);
    expect(unitsSpent())->toBe(1);
})->with([
    'bare handle' => '@MKBHD',
    'handle URL' => 'https://www.youtube.com/@mkbhd',
    'legacy user URL' => 'https://www.youtube.com/user/marquesbrownlee',
]);

test('re-adding an already resolved handle is a free local lookup', function () {
    fakeYoutube();
    [$user, $group] = subscriber();

    addChannel($user, $group, '@mkbhd')->assertSessionHasNoErrors();
    expect(unitsSpent())->toBe(1);

    [$other, $otherGroup] = subscriber();
    addChannel($other, $otherGroup, '@MKBHD')->assertSessionHasNoErrors();

    expect(apiCallCount())->toBe(1);
    expect(unitsSpent())->toBe(1);
    expect(Channel::where('channel_id', RESOLVED_ID)->count())->toBe(1);
});

test('a /c/ custom URL is rejected and points the user at the @handle', function () {
    fakeYoutube();
    [$user, $group] = subscriber();

    addChannel($user, $group, 'https://www.youtube.com/c/mkbhd')
        ->assertSessionHasErrors('value');

    expect(session('errors')->first('value'))->toContain('@handle');
    expect(apiCallCount())->toBe(0);
    expect(Channel::count())->toBe(0);
});

test('at the daily cap an API add is refused while a UC add still succeeds', function () {
    fakeYoutube();
    [$user, $group] = subscriber();

    YoutubeApiUsage::create([
        'date_pt' => (new YoutubeApiClient('fake-key'))->currentDate(),
        'units_used' => YoutubeApiClient::DAILY_CAP,
    ]);

    addChannel($user, $group, '@mkbhd')->assertSessionHasErrors('value');
    expect(session('errors')->first('value'))->toBe(YoutubeApiClient::CAP_MESSAGE);
    expect(apiCallCount())->toBe(0);

    addChannel($user, $group, RESOLVED_ID)->assertSessionHasNoErrors();

    expect(Channel::where('channel_id', RESOLVED_ID)->exists())->toBeTrue();
    expect(unitsSpent())->toBe(YoutubeApiClient::DAILY_CAP);
});

test('the counter is stored durably and rolls over at midnight America/Los_Angeles', function () {
    $client = new YoutubeApiClient('fake-key');

    // 06:30 UTC on 9 Sep is still 23:30 on 8 Sep in Pacific time.
    $this->travelTo(Carbon::parse('2026-09-09 06:30:00', 'UTC'));
    $client->consume();
    $client->consume();

    expect($client->currentDate())->toBe('2026-09-08');
    expect($client->unitsUsedToday())->toBe(2);

    // One hour later Pacific has crossed midnight, so the budget is fresh.
    $this->travelTo(Carbon::parse('2026-09-09 07:30:00', 'UTC'));

    expect($client->currentDate())->toBe('2026-09-09');
    expect($client->unitsUsedToday())->toBe(0);
    expect($client->remaining())->toBe(YoutubeApiClient::DAILY_CAP);

    // The earlier day's row survives.
    expect(YoutubeApiUsage::where('date_pt', '2026-09-08')->value('units_used'))->toBe(2);
});
