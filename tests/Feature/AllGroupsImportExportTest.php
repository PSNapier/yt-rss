<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(fn () => Config::set('services.youtube.api_key', null));

function allGroupsTestChannelId(string $suffix = 'A'): string
{
    return 'UC'.str_repeat($suffix, 22);
}

test('user can export all groups and channels as json', function () {
    $user = User::factory()->create();
    $g1 = ChannelGroup::factory()->for($user)->create(['name' => 'Alpha']);
    $g2 = ChannelGroup::factory()->for($user)->create(['name' => 'Beta']);
    $uc1 = allGroupsTestChannelId('B');
    $uc2 = allGroupsTestChannelId('C');
    $ch1 = Channel::factory()->create(['channel_id' => $uc1, 'name' => 'One']);
    $ch2 = Channel::factory()->create(['channel_id' => $uc2, 'name' => 'Two']);
    $g1->channels()->attach($ch1->id, ['is_favorite' => true]);
    $g2->channels()->attach($ch2->id, ['is_favorite' => false]);

    $response = $this->actingAs($user)->get(route('groups.export-all'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/json');
    $data = json_decode($response->getContent(), true);
    expect($data)->toBeArray()
        ->and($data['format'] ?? null)->toBe('yt-rss-all-groups')
        ->and($data['version'] ?? null)->toBe(1)
        ->and($data['groups'] ?? null)->toHaveCount(2)
        ->and($data['groups'][0]['name'])->toBe('Alpha')
        ->and($data['groups'][0]['channels'])->toHaveCount(1)
        ->and($data['groups'][0]['channels'][0]['channel_id'])->toBe($uc1)
        ->and($data['groups'][0]['channels'][0]['is_favorite'])->toBeTrue()
        ->and($data['groups'][1]['name'])->toBe('Beta')
        ->and($data['groups'][1]['channels'][0]['is_favorite'])->toBeFalse();
});

test('user can import all groups from global json', function () {
    $user = User::factory()->create();
    $uc1 = allGroupsTestChannelId('D');
    $uc2 = allGroupsTestChannelId('E');
    $json = json_encode([
        'format' => 'yt-rss-all-groups',
        'version' => 1,
        'groups' => [
            [
                'name' => 'Imported A',
                'channels' => [
                    ['channel_id' => $uc1, 'name' => 'Ch One', 'is_favorite' => true],
                ],
            ],
            [
                'name' => 'Imported B',
                'channels' => [
                    ['channel_id' => $uc2, 'is_favorite' => false],
                ],
            ],
        ],
    ]);
    $file = UploadedFile::fake()->createWithContent('all.json', $json);

    $this->actingAs($user)
        ->post(route('groups.import-all'), ['file' => $file])
        ->assertRedirect();

    expect($user->channelGroups()->count())->toBe(2);
    $a = $user->channelGroups()->where('name', 'Imported A')->first();
    expect($a)->not->toBeNull();
    expect($a->channels()->count())->toBe(1);
    $p = $a->channels()->first();
    expect($p->channel_id)->toBe($uc1)
        ->and($p->name)->toBe('Ch One')
        ->and((bool) $p->pivot->is_favorite)->toBeTrue();
});

test('import merges channels into existing group with same name', function () {
    $user = User::factory()->create();
    $existing = ChannelGroup::factory()->for($user)->create(['name' => 'Shared']);
    $uc = allGroupsTestChannelId('F');
    $ch = Channel::factory()->create(['channel_id' => $uc]);
    $existing->channels()->attach($ch->id, ['is_favorite' => false]);

    $json = json_encode([
        'groups' => [
            [
                'name' => 'Shared',
                'channels' => [
                    ['channel_id' => $uc, 'is_favorite' => true],
                ],
            ],
        ],
    ]);
    $file = UploadedFile::fake()->createWithContent('m.json', $json);

    $this->actingAs($user)
        ->post(route('groups.import-all'), ['file' => $file])
        ->assertRedirect();

    expect($user->channelGroups()->where('name', 'Shared')->count())->toBe(1);
    $pivot = $existing->fresh()->channels()->where('channels.id', $ch->id)->first();
    expect((bool) $pivot->pivot->is_favorite)->toBeTrue();
});

test('import rejects invalid json', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('bad.json', 'not json');

    $this->actingAs($user)
        ->post(route('groups.import-all'), ['file' => $file])
        ->assertSessionHasErrors('file');
});

test('import rejects payload without groups array', function () {
    $user = User::factory()->create();
    $json = json_encode(['format' => 'yt-rss-all-groups', 'channels' => []]);
    $file = UploadedFile::fake()->createWithContent('x.json', $json);

    $this->actingAs($user)
        ->post(route('groups.import-all'), ['file' => $file])
        ->assertSessionHasErrors('file');
});
