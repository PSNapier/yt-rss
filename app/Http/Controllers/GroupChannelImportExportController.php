<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportGroupChannelListRequest;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\GroupChannelListImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GroupChannelImportExportController extends Controller
{
    public function export(ChannelGroup $group): Response
    {
        $this->authorize('view', $group);

        $channels = $group->channels()
            ->withPivot('is_favorite')
            ->orderBy('channels.name')
            ->get(['channels.id', 'channels.channel_id', 'channels.name']);

        $payload = [
            'format' => 'yt-rss-group-channels',
            'version' => 1,
            'group' => [
                'name' => $group->name,
            ],
            'channels' => $channels
                ->map(fn (Channel $channel) => [
                    'channel_id' => $channel->channel_id,
                    'name' => $channel->name,
                    'is_favorite' => (bool) $channel->pivot->is_favorite,
                ])
                ->values()
                ->all(),
        ];

        $base = Str::slug($group->name);
        if ($base === '') {
            $base = 'group-'.$group->id;
        }
        $filename = $base.'-channels.json';

        $body = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return response($body, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function import(ImportGroupChannelListRequest $request, ChannelGroup $group, GroupChannelListImporter $importer): RedirectResponse
    {
        $this->authorize('update', $group);

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

        $rows = $this->extractChannelRows($decoded);
        if ($rows === []) {
            return back()->with('toast', [
                'type' => 'warning',
                'message' => __('No channels found in file.'),
            ]);
        }

        $result = $importer->import($group, $rows);

        $message = __('Imported :added, updated :updated in this group.', [
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
    protected function extractChannelRows(mixed $decoded): array
    {
        if (is_array($decoded) && isset($decoded['channels']) && is_array($decoded['channels'])) {
            return array_values($decoded['channels']);
        }
        if (is_array($decoded) && array_is_list($decoded)) {
            return $decoded;
        }

        throw ValidationException::withMessages([
            'file' => __('JSON must be an array of channel objects, or an object with a "channels" array (same format as export).'),
        ]);
    }
}
