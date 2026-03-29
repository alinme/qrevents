<?php

use App\Jobs\GenerateEventAssetImageVariants;
use App\Jobs\GenerateEventAssetVideoThumbnails;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventAssetComment;
use App\Models\EventAssetLike;
use App\Models\EventGuest;
use App\Models\TextPostTheme;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Process\Process;

beforeEach(function () {
    Config::set('filesystems.default', 'public');
    Config::set('events.upload_disk', 'public');
    Config::set('events.pre_event_test_upload_limit', 10);
    Storage::fake('public');
});

it('keeps new uploads off the photo wall until the owner approves them', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'moderation_enabled' => false,
        'auto_moderation_enabled' => false,
    ]);

    $this->post(route('events.album.upload', $event->share_token), [
        'files' => [
            UploadedFile::fake()->image('photo.jpg')->size(300),
        ],
        'guest_name' => 'Elena',
        'guest_intent' => 'upload_media',
    ])->assertRedirect();

    /** @var EventAsset $asset */
    $asset = $event->assets()->latest('id')->firstOrFail();

    expect($asset->moderation_status)->toBe('approved')
        ->and($asset->metadata['wall_visibility'] ?? null)->toBe('pending');

    $this->get(route('events.wall', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Wall')
            ->where('assets', []),
        );

    $this->actingAs($owner)
        ->patch(route('events.assets.wall-visibility.update', [$event, $asset]), [
            'wall_visibility' => 'approved',
        ])
        ->assertRedirect();

    $asset->refresh();

    expect($asset->metadata['wall_visibility'] ?? null)->toBe('approved');

    $this->get(route('events.wall', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Wall')
            ->where('assets.0.id', $asset->id),
        );
});

it('includes live reaction data for approved wall assets', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();
    $guest = EventGuest::factory()->for($event)->create([
        'name' => 'Elena',
    ]);

    /** @var EventAsset $asset */
    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/wall-photo.jpg',
        'preview_path' => 'events/test/wall-preview.jpg',
        'thumbnail_path' => 'events/test/wall-thumb.jpg',
        'width' => 1080,
        'height' => 1920,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'wall_visibility' => 'approved',
        ],
    ]);

    EventAssetLike::factory()->create([
        'event_asset_id' => $asset->id,
        'event_guest_id' => $guest->id,
    ]);

    EventAssetComment::query()->create([
        'event_asset_id' => $asset->id,
        'event_guest_id' => $guest->id,
        'body' => "Can't wait to see this on the wall.",
    ]);

    $this->get(route('events.wall', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Wall')
            ->where('assets.0.id', $asset->id)
            ->where('assets.0.previewUrl', route('events.album.asset-preview', [$event->publicAlbumCode(), $asset]))
            ->where('assets.0.thumbnailUrl', route('events.album.asset-thumbnail', [$event->publicAlbumCode(), $asset]))
            ->where('assets.0.width', 1080)
            ->where('assets.0.height', 1920)
            ->where('assets.0.likeCount', 1)
            ->where('assets.0.commentCount', 1)
            ->where('assets.0.recentComments.0.body', "Can't wait to see this on the wall.")
            ->where('assets.0.recentComments.0.guestName', 'Elena'),
        );
});

it('includes duration data for approved wall video assets', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    /** @var EventAsset $asset */
    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'video',
        'disk' => 'public',
        'path' => 'events/test/wall-video.mp4',
        'preview_path' => 'events/test/wall-video-preview.mp4',
        'video_thumbnail_path' => 'events/test/wall-video-thumb.jpg',
        'width' => 1080,
        'height' => 1920,
        'duration_seconds' => 17,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'wall_visibility' => 'approved',
        ],
    ]);

    $this->get(route('events.wall', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Wall')
            ->where('assets.0.id', $asset->id)
            ->where('assets.0.kind', 'video')
            ->where('assets.0.previewUrl', route('events.album.asset-preview', [$event->publicAlbumCode(), $asset]))
            ->where('assets.0.thumbnailUrl', route('events.album.asset-thumbnail', [$event->publicAlbumCode(), $asset]))
            ->where('assets.0.durationSeconds', 17),
        );
});

it('serves public asset previews and thumbnails through stable album routes', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    Storage::disk('public')->put('events/test/preview-photo.jpg', 'photo-preview');
    Storage::disk('public')->put('events/test/preview-thumb.jpg', 'photo-thumb');

    /** @var EventAsset $asset */
    $asset = EventAsset::factory()->for($event)->for($owner)->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/original-photo.jpg',
        'preview_path' => 'events/test/preview-photo.jpg',
        'thumbnail_path' => 'events/test/preview-thumb.jpg',
        'moderation_status' => 'approved',
        'metadata' => [
            'wall_visibility' => 'approved',
        ],
    ]);

    $this->get(route('events.album.asset-preview', [$event->publicAlbumCode(), $asset]))
        ->assertOk();

    $this->get(route('events.album.asset-thumbnail', [$event->publicAlbumCode(), $asset]))
        ->assertOk();
});

it('uploads photos and videos when the upload window is open', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'grace_ends_at' => now()->addDay(),
        'storage_limit_bytes' => 10 * 1024 * 1024,
        'storage_used_bytes' => 0,
        'upload_limit' => 10,
        'upload_count' => 0,
        'photo_max_size_bytes' => 5 * 1024 * 1024,
        'video_max_size_bytes' => 5 * 1024 * 1024,
    ]);

    $response = $this->post(route('events.album.upload', $event->share_token), [
        'files' => [
            UploadedFile::fake()->image('photo.jpg')->size(300),
            UploadedFile::fake()->create('clip.mp4', 600, 'video/mp4'),
        ],
        'guest_name' => 'Elena',
        'guest_intent' => 'upload_media',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $event->refresh();
    $assetNames = $event->assets()
        ->orderBy('id')
        ->pluck('original_filename')
        ->all();

    expect($event->upload_count)->toBe(2)
        ->and($event->assets()->count())->toBe(2)
        ->and($event->storage_used_bytes)->toBeGreaterThan(0)
        ->and($assetNames[0] ?? null)->toMatch('/^elena-(photo|video)-\d{8}-\d{6}-01\.[a-z0-9]+$/')
        ->and($assetNames[1] ?? null)->toMatch('/^elena-(photo|video)-\d{8}-\d{6}-02\.[a-z0-9]+$/');
});

it('optimizes large uploaded photos before storing them', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'photo_max_size_bytes' => 12 * 1024 * 1024,
    ]);

    $tmpPath = tempnam(sys_get_temp_dir(), 'qr-photo-');
    expect($tmpPath)->not->toBeFalse();

    $image = imagecreatetruecolor(3000, 1900);
    for ($x = 0; $x < 3000; $x += 60) {
        for ($y = 0; $y < 1900; $y += 60) {
            $color = imagecolorallocate($image, ($x / 8) % 255, ($y / 11) % 255, (($x + $y) / 13) % 255);
            imagefilledrectangle($image, $x, $y, min(2999, $x + 59), min(1899, $y + 59), $color);
        }
    }
    imagepng($image, $tmpPath);
    imagedestroy($image);

    $uploaded = new UploadedFile(
        $tmpPath,
        'huge-phone-photo.png',
        'image/png',
        null,
        true,
    );

    $response = $this->post(route('events.album.upload', $event->share_token), [
        'files' => [$uploaded],
        'guest_name' => 'Elena',
        'guest_intent' => 'upload_media',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    /** @var EventAsset $asset */
    $asset = $event->assets()->latest('id')->firstOrFail();

    expect($asset->mime_type)->toBe('image/jpeg')
        ->and($asset->original_filename)->toEndWith('.jpg')
        ->and((int) $asset->width)->toBeLessThanOrEqual(2560)
        ->and((int) $asset->height)->toBeLessThanOrEqual(2560)
        ->and((int) $asset->width)->not->toBe((int) $asset->height)
        ->and($asset->size_bytes)->toBeLessThan(12 * 1024 * 1024);

    $blob = Storage::disk('public')->get($asset->path);
    $imagick = new Imagick;
    $imagick->readImageBlob($blob);
    $imagick = $imagick->coalesceImages();
    $imagick->setIteratorIndex(0);

    expect(strtolower((string) $imagick->getImageFormat()))->toBe('jpeg')
        ->and((int) $imagick->getImageWidth())->toBeLessThanOrEqual(2560)
        ->and((int) $imagick->getImageHeight())->toBeLessThanOrEqual(2560);

    $imagick->clear();
    $imagick->destroy();

    @unlink($tmpPath);
});

it('stores an optional message with photo and video uploads', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $response = $this->post(route('events.album.upload', $event->share_token), [
        'files' => [
            UploadedFile::fake()->image('photo.jpg')->size(300),
        ],
        'guest_name' => 'Elena',
        'message' => 'We are so happy to celebrate with you both.',
        'guest_intent' => 'upload_media',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $asset = $event->assets()->latest('id')->first();

    expect($asset)->not->toBeNull()
        ->and($asset?->metadata['message'] ?? null)->toBe('We are so happy to celebrate with you both.');
});

it('generates thumbnail, preview, and watermarked variants for photo assets', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $image = imagecreatetruecolor(1200, 800);
    $background = imagecolorallocate($image, 220, 120, 90);
    imagefill($image, 0, 0, $background);
    ob_start();
    imagepng($image);
    $pngContents = (string) ob_get_clean();
    imagedestroy($image);

    $path = 'events/test/manual-source.png';
    Storage::disk('public')->put($path, $pngContents);

    /** @var EventAsset $asset */
    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => $path,
        'original_filename' => 'manual-source.png',
        'mime_type' => 'image/png',
        'size_bytes' => strlen($pngContents),
        'moderation_status' => 'approved',
        'is_watermarked' => false,
    ]);

    (new GenerateEventAssetImageVariants($asset->id))->handle();
    $asset->refresh();

    expect($asset->thumbnail_path)->not->toBeNull()
        ->and($asset->preview_path)->not->toBeNull()
        ->and($asset->watermarked_thumbnail_path)->not->toBeNull()
        ->and($asset->watermarked_preview_path)->not->toBeNull()
        ->and($asset->watermarked_download_path)->not->toBeNull()
        ->and($asset->is_watermarked)->toBeTrue();

    Storage::disk('public')->assertExists($asset->path);
    Storage::disk('public')->assertExists((string) $asset->thumbnail_path);
    Storage::disk('public')->assertExists((string) $asset->preview_path);
    Storage::disk('public')->assertExists((string) $asset->watermarked_thumbnail_path);
    Storage::disk('public')->assertExists((string) $asset->watermarked_preview_path);
    Storage::disk('public')->assertExists((string) $asset->watermarked_download_path);

    $thumb = new Imagick;
    $thumb->readImageBlob(Storage::disk('public')->get((string) $asset->thumbnail_path));
    $thumb = $thumb->coalesceImages();
    $thumb->setIteratorIndex(0);

    expect((int) $thumb->getImageWidth())->toBe(640)
        ->and((int) $thumb->getImageHeight())->toBeLessThan(640);

    $thumb->clear();
    $thumb->destroy();
});

it('generates one thumbnail and one watermarked preview video variant', function () {
    $ffmpegBinary = trim((string) shell_exec('command -v ffmpeg'));
    if ($ffmpegBinary === '') {
        test()->markTestSkipped('ffmpeg is not installed.');
    }

    Config::set('events.video_variants.ffmpeg_binary', $ffmpegBinary);

    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $videoPath = tempnam(sys_get_temp_dir(), 'qr-video-');
    expect($videoPath)->not->toBeFalse();
    $videoMp4Path = $videoPath.'.mp4';

    $process = new Process([
        $ffmpegBinary,
        '-y',
        '-f',
        'lavfi',
        '-i',
        'color=c=#d97706:s=1280x720:d=2',
        '-pix_fmt',
        'yuv420p',
        $videoMp4Path,
    ]);
    $process->setTimeout(60);
    $process->mustRun();

    Storage::disk('public')->put(
        'events/test/manual-video.mp4',
        file_get_contents($videoMp4Path),
    );

    /** @var EventAsset $asset */
    $asset = $event->assets()->create([
        'kind' => 'video',
        'disk' => 'public',
        'path' => 'events/test/manual-video.mp4',
        'original_filename' => 'manual-video.mp4',
        'mime_type' => 'video/mp4',
        'size_bytes' => filesize($videoMp4Path),
        'duration_seconds' => 2,
        'moderation_status' => 'approved',
        'is_watermarked' => false,
    ]);

    (new GenerateEventAssetVideoThumbnails($asset->id))->handle();
    $asset->refresh();

    expect($asset->video_thumbnail_path)->not->toBeNull()
        ->and($asset->video_preview_path)->not->toBeNull()
        ->and($asset->watermarked_video_thumbnail_path)->toBeNull()
        ->and($asset->watermarked_video_preview_path)->toBeNull()
        ->and($asset->watermarked_video_download_path)->toBeNull();

    Storage::disk('public')->assertExists((string) $asset->video_thumbnail_path);
    Storage::disk('public')->assertExists((string) $asset->video_preview_path);

    $thumb = new Imagick;
    $thumb->readImageBlob(Storage::disk('public')->get((string) $asset->video_thumbnail_path));
    $thumb = $thumb->coalesceImages();
    $thumb->setIteratorIndex(0);

    expect((int) $thumb->getImageWidth())->toBeLessThanOrEqual(960)
        ->and((int) $thumb->getImageHeight())->toBeLessThanOrEqual(960);

    $thumb->clear();
    $thumb->destroy();

    expect(Storage::disk('public')->size((string) $asset->video_preview_path))->toBeGreaterThan(0);

    @unlink($videoPath);
    @unlink($videoMp4Path);
});

it('rejects uploads when the upload window is closed', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subDays(3),
        'upload_window_ends_at' => now()->subDay(),
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->image('photo.jpg')->size(300)],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    expect($event->assets()->count())->toBe(0);
});

it('allows limited pre-event test uploads before the upload window opens', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->addDays(10),
        'upload_window_ends_at' => now()->addDays(13),
        'upload_count' => 9,
    ]);

    $response = $this->post(route('events.album.upload', $event->share_token), [
        'files' => [UploadedFile::fake()->image('test-photo.jpg')->size(300)],
        'guest_name' => 'Elena',
        'guest_intent' => 'upload_media',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $event->refresh();

    expect($event->upload_count)->toBe(10)
        ->and($event->assets()->count())->toBe(1);
});

it('rejects pre-event uploads once the test upload limit is reached', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->addDays(10),
        'upload_window_ends_at' => now()->addDays(13),
        'upload_count' => 10,
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->image('test-photo.jpg')->size(300)],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    $event->refresh();

    expect($event->upload_count)->toBe(10)
        ->and($event->assets()->count())->toBe(0);
});

it('rejects uploads when upload count limit is reached', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'upload_limit' => 1,
        'upload_count' => 1,
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->image('photo.jpg')->size(300)],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    $event->refresh();

    expect($event->upload_count)->toBe(1)
        ->and($event->assets()->count())->toBe(0);
});

it('rejects uploads when storage limit would be exceeded', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'storage_limit_bytes' => 1,
        'storage_used_bytes' => 1,
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->image('photo.jpg')->size(300)],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    $event->refresh();

    expect($event->storage_used_bytes)->toBe(1)
        ->and($event->assets()->count())->toBe(0);
});

it('rejects unsupported file types', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->create('notes.pdf', 30, 'application/pdf')],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    expect($event->assets()->count())->toBe(0);
});

it('creates text posts when text media type is enabled', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'branding' => [
            'allowed_media_types' => ['photo', 'video', 'text'],
        ],
    ]);
    $theme = TextPostTheme::query()->firstOrFail();

    $response = $this->post(route('events.album.text-post', $event->share_token), [
        'text' => 'Congratulations and best wishes for a beautiful life together!',
        'text_post_theme_id' => $theme->id,
        'guest_name' => 'Elena',
        'guest_intent' => 'text_wish',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $event->refresh();
    $asset = $event->assets()->latest('id')->first();

    expect($event->upload_count)->toBe(1)
        ->and($asset)->not->toBeNull()
        ->and($asset?->kind)->toBe('text')
        ->and($asset?->mime_type)->toBe('text/plain')
        ->and($asset?->metadata['text'] ?? null)->toBe('Congratulations and best wishes for a beautiful life together!')
        ->and($asset?->metadata['guest_name'] ?? null)->toBe('Elena')
        ->and($asset?->metadata['text_theme_id'] ?? null)->toBe($theme->id)
        ->and($asset?->metadata['text_theme_slug'] ?? null)->toBe($theme->slug)
        ->and($asset?->metadata['text_background_image_path'] ?? null)->toBe($theme->image_path)
        ->and($asset?->metadata['text_color'] ?? null)->toBe($theme->text_color);
});

it('keeps text wishes enabled for legacy events that still use the old default media selection', function () {
    $event = Event::factory()->create([
        'branding' => [
            'allowed_media_types' => ['photo', 'video'],
        ],
    ]);

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('allowTextPosts', true)
            ->where('allowedMediaTypes', ['photo', 'video', 'text']),
        );
});

it('groups album media collections by upload batch', function () {
    $event = Event::factory()->create();

    $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/adrian-1.jpg',
        'original_filename' => 'adrian-1.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 2048,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Adrian',
            'upload_batch_id' => 'batch-adrian',
            'upload_batch_index' => 0,
        ],
    ]);

    $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/elena-a-1.jpg',
        'original_filename' => 'elena-a-1.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 2048,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'upload_batch_id' => 'batch-elena-a',
            'upload_batch_index' => 0,
        ],
    ]);

    $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/elena-a-2.jpg',
        'original_filename' => 'elena-a-2.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 2048,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'upload_batch_id' => 'batch-elena-a',
            'upload_batch_index' => 1,
        ],
    ]);

    $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/elena-b-1.jpg',
        'original_filename' => 'elena-b-1.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 2048,
        'width' => 1080,
        'height' => 1920,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'upload_batch_id' => 'batch-elena-b',
            'upload_batch_index' => 0,
        ],
    ]);

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->has('assets', 4)
            ->where('assets.0.guestName', 'Elena')
            ->where('assets.1.guestName', 'Elena')
            ->where('assets.2.guestName', 'Elena')
            ->where('assets.3.guestName', 'Adrian')
            ->where('assets.0.width', 1080)
            ->where('assets.0.height', 1920)
            ->where('assets.0.galleryGroupKey', 'batch:batch-elena-b')
            ->where('assets.1.galleryGroupKey', 'batch:batch-elena-a')
            ->where('assets.2.galleryGroupKey', 'batch:batch-elena-a')
            ->where('assets.3.galleryGroupKey', 'batch:batch-adrian')
        );
});

it('shares localized public album strings from the event display language', function () {
    $event = Event::factory()->create([
        'branding' => [
            'display_language' => 'el',
        ],
    ]);

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('locale.current', 'el')
            ->where('locale.available', ['en', 'ro', 'el'])
            ->where('translations.public.album.kicker', 'Ψηφιακό άλμπουμ')
            ->where('translations.public.wall.live_photo_wall', 'Ζωντανό photowall')
        );
});

it('allows overriding the public album locale from the query string', function () {
    $event = Event::factory()->create([
        'branding' => [
            'display_language' => 'en',
        ],
    ]);

    $this->get(route('events.album', ['shareToken' => $event->share_token, 'lang' => 'ro']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('locale.current', 'ro')
            ->where('translations.public.album.kicker', 'Album digital')
            ->where('translations.public.album.nav.settings', 'Setări')
        );
});

it('stores public guest profiles with optional avatars', function () {
    $event = Event::factory()->create();

    $response = $this->post(route('events.album.guest-profile.upsert', $event->share_token), [
        'guest_token' => 'guest-avatar-token',
        'guest_name' => 'Elena',
        'guest_email' => 'elena@example.com',
        'guest_phone' => '+40 700 123 456',
        'guest_fields' => [
            'name' => 'Elena',
            'instagram' => '@elena',
        ],
        'guest_intent' => 'browse_gallery',
        'avatar_file' => UploadedFile::fake()->image('avatar.png')->size(120),
    ]);

    $response->assertOk()
        ->assertJsonPath('guest.name', 'Elena')
        ->assertJsonPath('guest.email', 'elena@example.com')
        ->assertJsonPath('guest.avatarUrl', fn (mixed $value) => is_string($value) && $value !== '');

    $guest = EventGuest::query()
        ->where('event_id', $event->id)
        ->where('guest_token', 'guest-avatar-token')
        ->first();

    expect($guest)->not->toBeNull()
        ->and($guest?->name)->toBe('Elena')
        ->and($guest?->avatar_path)->not->toBeNull();

    Storage::disk('public')->assertExists((string) $guest?->avatar_path);
});

it('allows public asset deletion only for the matching guest token', function () {
    $event = Event::factory()->create([
        'storage_used_bytes' => 4096,
        'upload_count' => 1,
    ]);

    Storage::disk('public')->put('events/test/delete-me.jpg', 'photo');

    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/delete-me.jpg',
        'original_filename' => 'delete-me.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'guest_token' => 'poster-token',
        ],
    ]);

    $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.asset-delete', [$event->share_token, $asset]), [
            'guest_name' => 'Elena',
            'guest_token' => 'wrong-token',
        ])
        ->assertForbidden();

    expect(EventAsset::query()->whereKey($asset->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists('events/test/delete-me.jpg');

    $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.asset-delete', [$event->share_token, $asset]), [
            'guest_name' => 'Elena',
            'guest_token' => 'poster-token',
        ])
        ->assertRedirect(route('events.album', $event->share_token));

    expect(EventAsset::query()->whereKey($asset->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('events/test/delete-me.jpg');
});

it('allows owners to delete media assets with a json request', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create([
        'storage_used_bytes' => 4096,
        'upload_count' => 1,
    ]);

    Storage::disk('public')->put('events/test/owner-delete.jpg', 'photo');

    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/owner-delete.jpg',
        'original_filename' => 'owner-delete.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
            'guest_token' => 'poster-token',
        ],
    ]);

    $this->actingAs($owner)
        ->deleteJson(route('events.assets.destroy', [$event, $asset]))
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
        ]);

    expect(EventAsset::query()->whereKey($asset->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('events/test/owner-delete.jpg');
});

it('does not allow name-only public asset deletion for legacy uploads', function () {
    $event = Event::factory()->create([
        'storage_used_bytes' => 4096,
        'upload_count' => 1,
    ]);

    Storage::disk('public')->put('events/test/legacy-delete.jpg', 'photo');

    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/legacy-delete.jpg',
        'original_filename' => 'legacy-delete.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Elena',
        ],
    ]);

    $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.asset-delete', [$event->share_token, $asset]), [
            'guest_name' => 'Elena',
            'guest_token' => 'poster-token',
        ])
        ->assertForbidden();

    expect(EventAsset::query()->whereKey($asset->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists('events/test/legacy-delete.jpg');
});

it('toggles likes for a public guest on an asset', function () {
    $event = Event::factory()->create();
    $guest = EventGuest::factory()->for($event)->create([
        'guest_token' => 'guest-like-token',
        'name' => 'Elena',
    ]);
    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/liked-photo.jpg',
        'original_filename' => 'liked-photo.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Adrian',
            'guest_token' => 'poster-token',
        ],
    ]);

    $firstResponse = $this->post(route('events.album.asset-like.toggle', [$event->share_token, $asset]), [
        'guest_token' => $guest->guest_token,
    ]);

    $firstResponse->assertOk()
        ->assertJsonPath('liked', true)
        ->assertJsonPath('likeCount', 1);

    expect(EventAssetLike::query()
        ->where('event_asset_id', $asset->id)
        ->where('event_guest_id', $guest->id)
        ->exists())->toBeTrue();

    $secondResponse = $this->post(route('events.album.asset-like.toggle', [$event->share_token, $asset]), [
        'guest_token' => $guest->guest_token,
    ]);

    $secondResponse->assertOk()
        ->assertJsonPath('liked', false)
        ->assertJsonPath('likeCount', 0);

    expect(EventAssetLike::query()
        ->where('event_asset_id', $asset->id)
        ->where('event_guest_id', $guest->id)
        ->exists())->toBeFalse();
});

it('lists and stores comments for a public asset', function () {
    $event = Event::factory()->create();
    $poster = EventGuest::factory()->for($event)->create([
        'guest_token' => 'poster-token',
        'name' => 'Poster',
    ]);
    $commenter = EventGuest::factory()->for($event)->create([
        'guest_token' => 'commenter-token',
        'name' => 'Elena',
    ]);
    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/commented-photo.jpg',
        'original_filename' => 'commented-photo.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => $poster->name,
            'guest_token' => $poster->guest_token,
        ],
    ]);
    EventAssetComment::query()->create([
        'event_asset_id' => $asset->id,
        'event_guest_id' => $poster->id,
        'body' => 'First comment',
    ]);

    $indexResponse = $this->get(route('events.album.asset-comments.index', [$event->share_token, $asset]));

    $indexResponse->assertOk()
        ->assertJsonPath('commentCount', 1)
        ->assertJsonPath('comments.0.body', 'First comment')
        ->assertJsonPath('comments.0.guestName', 'Poster');

    $storeResponse = $this->post(route('events.album.asset-comments.store', [$event->share_token, $asset]), [
        'guest_token' => $commenter->guest_token,
        'body' => 'Beautiful memory.',
    ]);

    $storeResponse->assertOk()
        ->assertJsonPath('comment.body', 'Beautiful memory.')
        ->assertJsonPath('comment.guestName', 'Elena')
        ->assertJsonPath('commentCount', 2);

    $commentId = (int) $storeResponse->json('comment.id');
    $comment = EventAssetComment::query()->findOrFail($commentId);

    $likeResponse = $this->post(route('events.album.asset-comment-like.toggle', [$event->share_token, $asset, $comment]), [
        'guest_token' => $poster->guest_token,
    ]);

    $likeResponse->assertOk()
        ->assertJsonPath('liked', true)
        ->assertJsonPath('likeCount', 1);

    expect(EventAssetComment::query()
        ->where('event_asset_id', $asset->id)
        ->where('event_guest_id', $commenter->id)
        ->where('body', 'Beautiful memory.')
        ->exists())->toBeTrue();
});

it('throttles repeated public guest profile writes', function () {
    config(['events.public_rate_limits.write_per_minute' => 3]);

    $event = Event::factory()->create();

    foreach (range(1, 3) as $attempt) {
        $this->post(route('events.album.guest-profile.upsert', $event->share_token), [
            'guest_token' => 'throttle-guest-token',
            'guest_name' => 'Elena',
            'guest_intent' => 'browse_gallery',
        ])->assertOk();
    }

    $this->post(route('events.album.guest-profile.upsert', $event->share_token), [
        'guest_token' => 'throttle-guest-token',
        'guest_name' => 'Elena',
        'guest_intent' => 'browse_gallery',
    ])->assertTooManyRequests();
});

it('hides gallery media when album permission is upload only', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'branding' => [
            'album_permission' => 'upload_only',
        ],
    ]);

    $asset = $event->assets()->create([
        'kind' => 'photo',
        'disk' => 'public',
        'path' => 'events/test/private-photo.jpg',
        'original_filename' => 'private-photo.jpg',
        'mime_type' => 'image/jpeg',
        'size_bytes' => 4096,
        'moderation_status' => 'approved',
        'metadata' => [
            'guest_name' => 'Poster',
            'guest_token' => 'poster-token',
        ],
    ]);

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->where('canViewGallery', false)
            ->where('isUploadAllowed', true)
            ->has('assets', 0)
        );

    $this->getJson(route('events.album.assets', $event->share_token))
        ->assertOk()
        ->assertJson([
            'assets' => [],
            'nextCursor' => null,
            'hasMore' => false,
        ]);

    $this->get(route('events.album.asset-comments.index', [$event->share_token, $asset]))
        ->assertForbidden();
});

it('rejects uploads when album permission is view only', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'branding' => [
            'album_permission' => 'view_only',
        ],
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.upload', $event->share_token), [
            'files' => [UploadedFile::fake()->image('photo.jpg')->size(300)],
            'guest_name' => 'Elena',
            'guest_intent' => 'upload_media',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('files');

    expect($event->assets()->count())->toBe(0);
});

it('queues uploads for manual moderation and keeps them out of the public album', function () {
    $event = Event::factory()->create([
        'moderation_enabled' => true,
        'auto_moderation_enabled' => false,
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
    ]);

    $response = $this->post(route('events.album.upload', $event->share_token), [
        'files' => [UploadedFile::fake()->image('photo.jpg')->size(300)],
        'guest_name' => 'Elena',
        'message' => 'A beautiful family moment.',
        'guest_intent' => 'upload_media',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $asset = $event->assets()->latest('id')->first();

    expect($asset)->not->toBeNull()
        ->and($asset?->moderation_status)->toBe('processing')
        ->and($asset?->reviewed_at)->toBeNull();

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->has('assets', 0)
        );
});

it('automatically rejects flagged text posts', function () {
    $event = Event::factory()->create([
        'moderation_enabled' => true,
        'auto_moderation_enabled' => true,
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'branding' => [
            'allowed_media_types' => ['photo', 'video', 'text'],
            'moderation_filters' => ['adult', 'nudity', 'violence', 'suggestive'],
        ],
    ]);
    $theme = TextPostTheme::query()->firstOrFail();

    $response = $this->post(route('events.album.text-post', $event->share_token), [
        'text' => 'This post contains nude content.',
        'text_post_theme_id' => $theme->id,
        'guest_name' => 'Elena',
        'guest_intent' => 'text_wish',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $asset = $event->assets()->latest('id')->first();

    expect($asset)->not->toBeNull()
        ->and($asset?->moderation_status)->toBe('rejected')
        ->and($asset?->moderation_score)->toBeGreaterThan(0)
        ->and($asset?->reviewed_at)->not->toBeNull()
        ->and($asset?->metadata['moderation']['pipeline'] ?? null)->toBe('automatic');

    $this->get(route('events.album', $event->share_token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/Album')
            ->has('assets', 0)
        );
});

it('rejects text posts when text media type is disabled', function () {
    $event = Event::factory()->create([
        'upload_window_starts_at' => now()->subHour(),
        'upload_window_ends_at' => now()->addHour(),
        'branding' => [
            'allowed_media_types' => ['photo', 'video'],
            'allowed_media_types_version' => 2,
        ],
    ]);

    $response = $this->from(route('events.album', $event->share_token))
        ->post(route('events.album.text-post', $event->share_token), [
            'text' => 'This should not be accepted.',
            'text_post_theme_id' => TextPostTheme::query()->firstOrFail()->id,
            'guest_name' => 'Elena',
            'guest_intent' => 'text_wish',
        ]);

    $response->assertRedirect(route('events.album', $event->share_token));
    $response->assertSessionHasErrors('text');

    expect($event->assets()->count())->toBe(0);
});
