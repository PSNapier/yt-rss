<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $results = Channel::where('name', 'like', '%'.$validated['q'].'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'channel_id', 'name']);

        return response()->json($results);
    }
}
