<?php

namespace App\Console\Commands;

use App\Models\UserVideoState;
use App\Models\Video;
use App\Services\YoutubeShortsDetector;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('videos:prune-shorts {--dry-run : List Shorts without deleting} {--sleep=150 : Milliseconds between YouTube requests}')]
#[Description('Remove stored Shorts by checking each video page (RSS only covers recent items)')]
class PruneShortVideosCommand extends Command
{
    public function handle(YoutubeShortsDetector $detector): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sleepMs = max(0, (int) $this->option('sleep'));

        $removed = 0;
        $uncertain = 0;

        Video::query()->orderBy('id')->chunkById(40, function ($videos) use ($detector, $dryRun, $sleepMs, &$removed, &$uncertain): void {
            foreach ($videos as $video) {
                $isShort = $detector->isShortByWatchPage($video->youtube_video_id);

                if ($isShort === null) {
                    $uncertain++;
                    $this->components->warn("Could not classify: {$video->youtube_video_id}");
                } elseif ($isShort === true) {
                    if ($dryRun) {
                        $this->line("[dry-run] Short: {$video->youtube_video_id} — {$video->title}");
                    } else {
                        DB::transaction(function () use ($video): void {
                            UserVideoState::query()->where('youtube_video_id', $video->youtube_video_id)->delete();
                            $video->delete();
                        });
                        $this->line("Removed Short: {$video->youtube_video_id}");
                    }
                    $removed++;
                }

                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }
            }
        });

        $this->components->info($dryRun
            ? "Dry run: {$removed} Short(s); {$uncertain} uncertain."
            : "Removed {$removed} Short(s); {$uncertain} uncertain.");

        return self::SUCCESS;
    }
}
