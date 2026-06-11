<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventOnboardingController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
    'pwaLaunch' => false,
])->name('home');

Route::inertia('/launch', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
    'pwaLaunch' => true,
])->name('launch');

Route::get('auth/google/redirect', [SocialAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('onboarding', [EventOnboardingController::class, 'create'])->name('onboarding.create');

Route::middleware(['auth'])->group(function () {
    Route::post('onboarding', [EventOnboardingController::class, 'store'])->name('onboarding.store');
    Route::get('onboarding/{event}/creating', [EventOnboardingController::class, 'creating'])->name('onboarding.creating');
    Route::get('onboarding/{event}/photos', [EventOnboardingController::class, 'photos'])->name('onboarding.photos');
    Route::get('onboarding/{event}/ready', [EventOnboardingController::class, 'ready'])->name('onboarding.ready');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/events', [DashboardController::class, 'ownedEvents'])->name('dashboard.events');
    Route::get('dashboard/activity', [DashboardController::class, 'recentActivity'])->name('dashboard.activity');

    Route::get('admin', [AdminController::class, 'index'])->name('admin.overview');
    Route::get('admin/events', [AdminController::class, 'events'])->name('admin.events');
    Route::get('admin/plans', [AdminController::class, 'plans'])->name('admin.plans');
    Route::post('admin/plans', [AdminController::class, 'storePlan'])->name('admin.plans.store');
    Route::patch('admin/plans/{plan}', [AdminController::class, 'updatePlan'])->name('admin.plans.update');

    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/print-pack', [EventController::class, 'printPack'])->name('events.print-pack');
    Route::get('events/{event}/print-pack/preview', [EventController::class, 'printPackPreview'])->name('events.print-pack.preview');
    Route::get('events/{event}/media', [EventController::class, 'media'])->name('events.media');
    Route::post('events/{event}/exports/media', [EventController::class, 'startMediaExport'])->name('events.exports.media.start');
    Route::get('events/{event}/exports/media/download', [EventController::class, 'downloadMediaExport'])->name('events.exports.media.download');
    Route::post('events/{event}/assets/bulk-delete', [EventController::class, 'bulkDestroyAssets'])->name('events.assets.bulk-destroy');
    Route::post('events/{event}/assets/bulk-moderation', [EventController::class, 'bulkUpdateAssetModeration'])->name('events.assets.bulk-moderation');
    Route::delete('events/{event}/assets/{asset}', [EventController::class, 'destroyAsset'])->name('events.assets.destroy');
    Route::patch('events/{event}/assets/{asset}/moderation', [EventController::class, 'updateAssetModeration'])->name('events.assets.moderation.update');
    Route::patch('events/{event}/assets/{asset}/wall-visibility', [EventController::class, 'updateAssetWallVisibility'])->name('events.assets.wall-visibility.update');
    Route::get('events/{event}/settings', [EventController::class, 'settings'])->name('events.settings');
    Route::patch('events/{event}/settings', [EventController::class, 'updateSettings'])->name('events.settings.update');
    Route::patch('events/{event}/billing', [EventController::class, 'updateBilling'])->name('events.billing.update');
    Route::post('events/{event}/billing/checkout', [EventController::class, 'createBillingCheckout'])->name('events.billing.checkout');
});
Route::post('stripe/webhook', StripeWebhookController::class)
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('stripe.webhook');
Route::get('album', [EventController::class, 'albumAccess'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.access.show');
Route::post('album', [EventController::class, 'resolveAlbumAccess'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.access.resolve');

Route::get('a/{shareToken}', [EventController::class, 'album'])->name('events.album');
Route::get('a/{shareToken}/assets', [EventController::class, 'albumAssets'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.assets');
Route::get('a/{shareToken}/guest-profile', [EventController::class, 'guestProfile'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.guest-profile.show');
Route::post('a/{shareToken}/guest-profile', [EventController::class, 'upsertGuestProfile'])
    ->middleware('throttle:public-album-write')
    ->name('events.album.guest-profile.upsert');
Route::post('a/{shareToken}/uploads', [EventController::class, 'upload'])
    ->middleware('throttle:public-album-upload')
    ->name('events.album.upload');
Route::post('a/{shareToken}/text-posts', [EventController::class, 'postText'])
    ->middleware('throttle:public-album-upload')
    ->name('events.album.text-post');
Route::post('a/{shareToken}/assets/{asset}/likes/toggle', [EventController::class, 'toggleAssetLike'])
    ->middleware('throttle:public-album-write')
    ->name('events.album.asset-like.toggle');
Route::get('a/{shareToken}/assets/{asset}/comments', [EventController::class, 'assetComments'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.asset-comments.index');
Route::post('a/{shareToken}/assets/{asset}/comments', [EventController::class, 'storeAssetComment'])
    ->middleware('throttle:public-album-write')
    ->name('events.album.asset-comments.store');
Route::post('a/{shareToken}/assets/{asset}/comments/{comment}/likes/toggle', [EventController::class, 'toggleAssetCommentLike'])
    ->middleware('throttle:public-album-write')
    ->name('events.album.asset-comment-like.toggle');
Route::get('a/{shareToken}/assets/{asset}/download', [EventController::class, 'downloadPublicAsset'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.asset-download');
Route::get('a/{shareToken}/assets/{asset}/preview', [EventController::class, 'publicAssetPreview'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.asset-preview');
Route::get('a/{shareToken}/assets/{asset}/thumbnail', [EventController::class, 'publicAssetThumbnail'])
    ->middleware('throttle:public-album-read')
    ->name('events.album.asset-thumbnail');
Route::post('a/{shareToken}/assets/{asset}/delete', [EventController::class, 'deletePublicAsset'])
    ->middleware('throttle:public-album-write')
    ->name('events.album.asset-delete');
Route::get('w/{shareToken}', [EventController::class, 'wall'])->name('events.wall');
Route::get('wall/{shareToken}', function (string $shareToken) {
    return redirect()->route('events.wall', $shareToken);
})->name('events.wall.legacy');

require __DIR__.'/settings.php';
