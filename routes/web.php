<?php

use App\Http\Controllers\AllGroupsImportExportController;
use App\Http\Controllers\AllVideosFeedController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChannelGroupController;
use App\Http\Controllers\GroupFeedController;
use App\Http\Controllers\SubscriptionController;
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

    Route::get('channels/search', [ChannelController::class, 'search'])->name('channels.search');

    Route::get('feed', [AllVideosFeedController::class, 'index'])->name('feed.index');

    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::patch('subscriptions/{channel}/groups', [SubscriptionController::class, 'updateGroups'])->name('subscriptions.update-groups');
    Route::patch('subscriptions/{channel}/favorite', [SubscriptionController::class, 'toggleFavorite'])->name('subscriptions.toggle-favorite');
    Route::delete('subscriptions/{channel}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');

    Route::post('videos/{youtubeVideoId}/state', [VideoStateController::class, 'store'])->name('videos.state.store');
});

require __DIR__.'/settings.php';
