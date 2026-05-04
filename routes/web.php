<?php

use App\Http\Controllers\AllGroupsImportExportController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChannelGroupController;
use App\Http\Controllers\GroupChannelImportExportController;
use App\Http\Controllers\GroupFeedController;
use App\Http\Controllers\VideoStateController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('groups', [ChannelGroupController::class, 'index'])->name('groups.index');
    Route::get('groups/export-all', [AllGroupsImportExportController::class, 'exportAll'])->name('groups.export-all');
    Route::post('groups/import-all', [AllGroupsImportExportController::class, 'importAll'])->name('groups.import-all');
    Route::post('groups', [ChannelGroupController::class, 'store'])->name('groups.store');
    Route::patch('groups/{group}', [ChannelGroupController::class, 'update'])->name('groups.update');
    Route::delete('groups/{group}', [ChannelGroupController::class, 'destroy'])->name('groups.destroy');

    Route::get('groups/{group}', [GroupFeedController::class, 'show'])->name('groups.show');
    Route::post('groups/{group}/refresh', [GroupFeedController::class, 'refresh'])->name('groups.refresh');

    Route::get('groups/{group}/channels', [ChannelController::class, 'index'])->name('groups.channels.index');
    Route::get('groups/{group}/channels/export', [GroupChannelImportExportController::class, 'export'])->name('groups.channels.export');
    Route::post('groups/{group}/channels/import', [GroupChannelImportExportController::class, 'import'])->name('groups.channels.import');
    Route::post('groups/{group}/channels', [ChannelController::class, 'store'])->name('groups.channels.store');
    Route::patch('groups/{group}/channels/{channel}', [ChannelController::class, 'update'])->name('groups.channels.update');
    Route::delete('groups/{group}/channels/{channel}', [ChannelController::class, 'destroy'])->name('groups.channels.destroy');

    Route::post('videos/{youtubeVideoId}/state', [VideoStateController::class, 'store'])->name('videos.state.store');
});

require __DIR__.'/settings.php';
