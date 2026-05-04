<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportAllGroupsChannelListRequest;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\AllGroupsChannelListImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AllGroupsImportExportController extends Controller
{
    public function exportAll(): Response
    {
        $user = request()->user();
        if ($user === null) {
            abort(403);
        }

        $groups = ChannelGroup::query()
            ->where('user_id', $user->id)
            ->with([
                'channels' => function ($query): void {
                    $query->withPivot('is_favorite')->orderBy('channels.name');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name']);

        $payload = [
            'format' => 'yt-rss-all-groups',
            'version' => 1,
            'groups' => $groups->map(function (ChannelGroup $group) {
                $channels = $group->channels;

                return [
                    'name' => $group->name,
                    'channels' => $channels
                        ->map(fn (Channel $channel) => [
                            'channel_id' => $channel->channel_id,
                            'name' => $channel->name,
                            'is_favorite' => (bool) $channel->pivot->is_favorite,
                        ])
                        ->values()
                        ->all(),
                ];
            })->values()->all(),
        ];

        $filename = 'yt-rss-all-groups.json';

        $body = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return response($body, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function importAll(ImportAllGroupsChannelListRequest $request, AllGroupsChannelListImporter $importer): RedirectResponse
    {
        $path = $request->file('file')?->getRealPath();
        if ($path === null || $path === false) {
            throw ValidationException::withMessages(['file' => __('Upload failed.')]);
        }

        $content = file_get_contents($path);
        if (! is_string($content)) {
            throw ValidationException::withMessages(['file' => __('Could not read file.')]);
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValidationException::withMessages([
                'file' => 'Invalid JSON: '.$e->getMessage(),
            ]);
        }

        $groups = $this->extractGroupEntries($decoded);
        if ($groups === []) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => __('No groups found in file.'),
            ]);
        }

        $user = $request->user();
        if ($user === null) {
            abort(403);
        }

        $result = $importer->import($user, $groups);

        $message = __('Imported :groups group(s): :added channel(s) added, :updated updated.', [
            'groups' => $result['groups_processed'],
            'added' => $result['added'],
            'updated' => $result['updated'],
        ]);
        if ($result['errors'] !== []) {
            $message .= ' '.implode(' ', $result['errors']);
        }

        $type = $result['errors'] === [] ? 'success' : 'warning';

        return back()->with('toast', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function extractGroupEntries(mixed $decoded): array
    {
        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'file' => __('JSON must be an object with a "groups" array (same format as export).'),
            ]);
        }

        if (isset($decoded['groups']) && is_array($decoded['groups'])) {
            return array_values($decoded['groups']);
        }

        throw ValidationException::withMessages([
            'file' => __('JSON must include a "groups" array (global export format).'),
        ]);
    }
}
