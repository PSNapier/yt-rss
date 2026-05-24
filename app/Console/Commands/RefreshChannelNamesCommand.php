<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Services\ChannelResolver;
use Illuminate\Console\Command;

class RefreshChannelNamesCommand extends Command
{
    protected $signature = 'channels:refresh-names';

    protected $description = 'Backfill channel names from their YouTube RSS feeds';

    public function handle(ChannelResolver $resolver): int
    {
        $channels = Channel::all();

        if ($channels->isEmpty()) {
            $this->info('No channels found.');

            return self::SUCCESS;
        }

        $updated = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($channels->count());
        $bar->start();

        $failures = [];

        foreach ($channels as $channel) {
            $name = $resolver->lookupChannelNameFromRss($channel->channel_id);

            if ($name !== null && $name !== $channel->name) {
                $channel->update(['name' => $name]);
                $updated++;
            } elseif ($name === null) {
                $failures[] = $channel->channel_id;
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Updated: {$updated}, unchanged: " . ($channels->count() - $updated - $failed) . ", failed: {$failed}.");

        if ($failures !== []) {
            $this->warn('Failed channel IDs:');
            foreach ($failures as $id) {
                $this->line("  {$id}");
            }
        }

        return self::SUCCESS;
    }
}
