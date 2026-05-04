<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\ChannelGroup;

class GroupChannelListImporter
{
    public const int MAX_ROWS = 2000;

    public function __construct(
        protected ChannelResolver $resolver
    ) {}

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{added: int, updated: int, errors: list<string>}
     */
    public function import(ChannelGroup $group, array $rows): array
    {
        $added = 0;
        $updated = 0;
        $errors = [];

        if (count($rows) > self::MAX_ROWS) {
            $errors[] = 'Too many rows (max '.self::MAX_ROWS.').';

            return ['added' => 0, 'updated' => 0, 'errors' => $errors];
        }

        foreach ($rows as $i => $row) {
            $line = $i + 1;
            if (! is_array($row)) {
                $errors[] = "Row {$line}: expected an object per channel.";

                continue;
            }

            $channelId = $row['channel_id'] ?? null;
            if (! is_string($channelId) || trim($channelId) === '') {
                $errors[] = "Row {$line}: missing or invalid channel_id.";

                continue;
            }

            try {
                $info = $this->resolver->fromChannelId(trim($channelId));
            } catch (\Throwable $e) {
                $errors[] = "Row {$line}: {$e->getMessage()}";

                continue;
            }

            $channel = Channel::query()->firstOrCreate(
                ['channel_id' => $info['channel_id']],
                ['name' => $info['name'], 'rss_url' => $info['rss_url']]
            );

            if (isset($row['name']) && is_string($row['name'])) {
                $customName = trim($row['name']);
                if ($customName !== '') {
                    $channel->update(['name' => $customName]);
                }
            }

            $isFavorite = false;
            if (array_key_exists('is_favorite', $row)) {
                $isFavorite = $this->coerceBoolean($row['is_favorite']);
            }

            $exists = $group->channels()->where('channels.id', $channel->id)->exists();
            if ($exists) {
                $group->channels()->updateExistingPivot($channel->id, [
                    'is_favorite' => $isFavorite,
                ]);
                $updated++;
            } else {
                $group->channels()->attach($channel->id, [
                    'is_favorite' => $isFavorite,
                ]);
                $added++;
            }
        }

        return ['added' => $added, 'updated' => $updated, 'errors' => $errors];
    }

    protected function coerceBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }
        if (is_string($value)) {
            $lower = strtolower(trim($value));

            return in_array($lower, ['1', 'true', 'yes', 'on'], true);
        }

        return (bool) $value;
    }
}
