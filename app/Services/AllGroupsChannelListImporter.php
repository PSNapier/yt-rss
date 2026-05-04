<?php

namespace App\Services;

use App\Models\ChannelGroup;
use App\Models\User;

class AllGroupsChannelListImporter
{
    public const int MAX_GROUPS = 200;

    public function __construct(
        protected GroupChannelListImporter $groupChannelListImporter
    ) {}

    /**
     * @param  list<array<string, mixed>>  $groupEntries
     * @return array{groups_processed: int, added: int, updated: int, errors: list<string>}
     */
    public function import(User $user, array $groupEntries): array
    {
        $groupsProcessed = 0;
        $added = 0;
        $updated = 0;
        $errors = [];

        if (count($groupEntries) > self::MAX_GROUPS) {
            $errors[] = 'Too many groups (max '.self::MAX_GROUPS.').';

            return ['groups_processed' => 0, 'added' => 0, 'updated' => 0, 'errors' => $errors];
        }

        foreach ($groupEntries as $i => $entry) {
            $line = $i + 1;
            if (! is_array($entry)) {
                $errors[] = "Group {$line}: expected an object per group.";

                continue;
            }

            $name = $entry['name'] ?? null;
            if (! is_string($name) || trim($name) === '') {
                $errors[] = "Group {$line}: missing or invalid name.";

                continue;
            }

            $channels = $entry['channels'] ?? [];
            if (! is_array($channels)) {
                $errors[] = "Group {$line} (\"{$name}\"): \"channels\" must be an array.";

                continue;
            }

            $group = ChannelGroup::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => trim($name),
                ],
            );

            $result = $this->groupChannelListImporter->import($group, array_values($channels));
            $groupsProcessed++;
            $added += $result['added'];
            $updated += $result['updated'];
            foreach ($result['errors'] as $err) {
                $errors[] = "Group \"{$name}\": {$err}";
            }
        }

        return [
            'groups_processed' => $groupsProcessed,
            'added' => $added,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }
}
