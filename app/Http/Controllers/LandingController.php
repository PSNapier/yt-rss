<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Where login and the logo land: the first visible group in sidebar order,
     * or All Videos when there is none.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $group = $request->user()
            ->channelGroups()
            ->visible()
            ->orderBy('name')
            ->first(['id']);

        return $group === null
            ? to_route('feed.index')
            : to_route('groups.show', $group);
    }
}
