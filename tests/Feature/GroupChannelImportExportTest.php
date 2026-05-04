<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(fn () => Config::set('services.youtube.api_key', null));

function testValidChannelId(string $suffix = 'A'): string
{
    return 'UC'.str_repeat($suffix, 22);
}

test('owner can export group channels as json', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'My Group']);
    $uc = testValidChannelId('B');
    $channel = Channel::factory()->create([
        'channel_id' => $uc,
        'name' => 'Test Channel',
    ]);
    $group->channels()->attach($channel->id, ['is_favorite' => true]);

    $response = $this->actingAs($user)->get(route('groups.channels.export', $group));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/json');
    $data = json_decode($response->getContent(), true);
    expect($data)->toBeArray()
        ->and($data['format'] ?? null)->toBe('yt-rss-group-channels')
        ->and($data['version'] ?? null)->toBe(1)
        ->and($data['group']['name'] ?? null)->toBe('My Group')
        ->and($data['channels'] ?? null)->toHaveCount(1)
        ->and($data['channels'][0]['channel_id'] ?? null)->toBe($uc)
        ->and($data['channels'][0]['name'] ?? null)->toBe('Test Channel')
        ->and($data['channels'][0]['is_favorite'] ?? null)->toBeTrue();
});

test('other user cannot export group channels', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();

    $this->actingAs($other)->get(route('groups.channels.export', $group))
        ->assertForbidden();
});

test('owner can import channels from json file', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $uc1 = testValidChannelId('C');
    $uc2 = testValidChannelId('D');
    $json = json_encode([
        'format' => 'yt-rss-group-channels',
        'channels' => [
            ['channel_id' => $uc1, 'name' => 'One', 'is_favorite' => true],
            ['channel_id' => $uc2, 'is_favorite' => false],
        ],
    ]);
    $file = UploadedFile::fake()->createWithContent('channels.json', $json);

    $this->actingAs($user)
        ->post(route('groups.channels.import', $group), ['file' => $file])
        ->assertRedirect();

    $group->refresh();
    expect($group->channels()->count())->toBe(2);
    $c1 = Channel::query()->where('channel_id', $uc1)->first();
    expect($c1)->not->toBeNull()
        ->and($c1->name)->toBe('One');
    $pivot = $group->channels()->where('channels.id', $c1->id)->first();
    expect((bool) $pivot->pivot->is_favorite)->toBeTrue();
});

test('import accepts bare array of channel objects', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $uc = testValidChannelId('G');
    $json = json_encode([
        ['channel_id' => $uc],
    ]);
    $file = UploadedFile::fake()->createWithContent('list.json', $json);

    $this->actingAs($user)
        ->post(route('groups.channels.import', $group), ['file' => $file])
        ->assertRedirect();

    expect($group->refresh()->channels()->count())->toBe(1);
});

test('import updates existing attach and favorite', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $uc = testValidChannelId('E');
    $channel = Channel::factory()->create(['channel_id' => $uc, 'name' => 'Old']);
    $group->channels()->attach($channel->id, ['is_favorite' => false]);

    $json = json_encode([
        'channels' => [
            ['channel_id' => $uc, 'name' => 'New Name', 'is_favorite' => true],
        ],
    ]);
    $file = UploadedFile::fake()->createWithContent('x.json', $json);

    $this->actingAs($user)
        ->post(route('groups.channels.import', $group), ['file' => $file])
        ->assertRedirect();

    $channel->refresh();
    expect($channel->name)->toBe('New Name');
    $pivot = $group->channels()->where('channels.id', $channel->id)->first();
    expect((bool) $pivot->pivot->is_favorite)->toBeTrue();
    expect($group->channels()->count())->toBe(1);
});

test('import rejects invalid json with validation error', function () {
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create();
    $file = UploadedFile::fake()->createWithContent('bad.json', 'not json');

    $this->actingAs($user)
        ->post(route('groups.channels.import', $group), ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('import rejects wrong user', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $group = ChannelGroup::factory()->for($owner)->create();
    $json = json_encode(['channels' => [['channel_id' => testValidChannelId('F')]]]);
    $file = UploadedFile::fake()->createWithContent('c.json', $json);

    $this->actingAs($other)
        ->post(route('groups.channels.import', $group), ['file' => $file])
        ->assertForbidden();
});
