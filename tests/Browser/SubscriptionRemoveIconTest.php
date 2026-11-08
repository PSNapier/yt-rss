<?php

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\User;

function removeIconFixture(): User
{
    $user = User::factory()->create();
    $group = ChannelGroup::factory()->for($user)->create(['name' => 'Tech']);
    $channel = Channel::factory()->create(['name' => 'Trashable Channel']);
    $group->channels()->attach($channel);

    return $user;
}

$trash = 'button[aria-label="Remove subscription"]';
$noTextRemove = "[...document.querySelectorAll('li button')].every(b => b.textContent.trim() !== 'Remove')";

it('shows a trash icon remove button in both views', function () use ($trash, $noTextRemove) {
    $this->actingAs(removeIconFixture());

    // Default is the by-group view.
    $page = visit(route('subscriptions.index'))
        ->assertSee('Trashable Channel')
        ->assertPresent($trash)
        ->assertPresent("$trash svg")
        ->assertScript($noTextRemove);

    $page->click('A–Z')
        ->assertPresent($trash)
        ->assertPresent("$trash svg")
        ->assertScript($noTextRemove)
        ->assertNoJavaScriptErrors();
});

it('opens the confirm dialog from the trash button', function () use ($trash) {
    $this->actingAs(removeIconFixture());

    visit(route('subscriptions.index'))
        ->assertScript("document.querySelector('$trash').className.includes('text-destructive')")
        ->click($trash)
        ->assertSee('Remove subscription?')
        ->assertSee('Trashable Channel')
        ->assertNoJavaScriptErrors();
});

it('labels the trash button for screen readers', function () use ($trash) {
    $this->actingAs(removeIconFixture());

    visit(route('subscriptions.index'))
        ->assertAttribute($trash, 'aria-label', 'Remove subscription');
});
