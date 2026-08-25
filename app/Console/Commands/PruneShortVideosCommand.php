<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YoutubeShortsDetector;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('videos:prune-shorts {--dry-run : List Shorts without flagging} {--sleep=150 : Milliseconds between YouTube requests}')]
#[Description('Flag stored Shorts by checking each video page (RSS only covers recent items)')]
class PruneShortVideosCommand extends Command
{
    /**
     * The watch-page check is the wider net: RSS only carries the last 15 entries, so
     * anything older is only reachable here. It flags rather than deletes, matching
     * `RssFetcher::ingest`, and never touches `user_video_states`: a misclassification
     * should cost a hidden row, not the user's watched state.
     */
    public function handle(YoutubeShortsDetector $detector): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sleepMs = max(0, (int) $this->option('sleep'));

        $flagged = 0;
        $uncertain = 0;

        // Already-flagged rows are skipped, so a re-run costs one request per video
        // that is still unclassified rather than one per video in the table.
        Video::query()
            ->where('is_short', false)
            ->orderBy('id')
            ->chunkById(40, function ($videos) use ($detector, $dryRun, $sleepMs, &$flagged, &$uncertain): void {
                foreach ($videos as $video) {
                    $isShort = $detector->isShortByWatchPage($video->youtube_video_id);

                    if ($isShort === null) {
                        $uncertain++;
                        $this->components->warn("Could not classify: {$video->youtube_video_id}");
                    } elseif ($isShort === true) {
                        if ($dryRun) {
                            $this->line("[dry-run] Short: {$video->youtube_video_id} - {$video->title}");
                        } else {
                            $video->forceFill(['is_short' => true])->save();
                            $this->line("Flagged Short: {$video->youtube_video_id}");
                        }
                        $flagged++;
                    }

                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }
                }
            });

        $this->components->info($dryRun
            ? "Dry run: {$flagged} Short(s); {$uncertain} uncertain."
            : "Flagged {$flagged} Short(s); {$uncertain} uncertain.");

        return self::SUCCESS;
    }
}
