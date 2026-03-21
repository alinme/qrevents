<?php

namespace App\Console\Commands;

use App\Jobs\GenerateEventAssetImageVariants;
use App\Jobs\GenerateEventAssetVideoThumbnails;
use App\Models\Event;
use App\Models\EventAsset;
use App\Models\EventAssetComment;
use App\Models\EventAssetCommentLike;
use App\Models\EventAssetLike;
use App\Models\EventGuest;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Http\File as HttpFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;

class SeedFakeEventAlbum extends Command
{
    protected $signature = 'events:seed-fake-album
        {event : Event id or share token}
        {--guests=100 : Number of fake guests}
        {--posts-per-guest=3 : Number of posts per fake guest}
        {--max-photos=10 : Maximum photos per post}
        {--video-chance=35 : Percent chance a post gets one video}
        {--max-likes=8 : Maximum likes on a generated post}
        {--max-comments=3 : Maximum comments on a generated post}
        {--batch-id= : Optional batch id for later cleanup}
        {--dry-run : Preview without writing data}';

    protected $description = 'Seed a public event album with reversible fake guests, posts, likes, and comments.';

    /** @var list<string> */
    private array $writtenPaths = [];

    public function handle(): int
    {
        $event = $this->resolveEvent((string) $this->argument('event'));
        $guestsToCreate = max(1, (int) $this->option('guests'));
        $postsPerGuest = max(1, (int) $this->option('posts-per-guest'));
        $maxPhotos = max(1, min(10, (int) $this->option('max-photos')));
        $videoChance = max(0, min(100, (int) $this->option('video-chance')));
        $maxLikes = max(0, (int) $this->option('max-likes'));
        $maxComments = max(0, (int) $this->option('max-comments'));
        $batchId = trim((string) ($this->option('batch-id') ?: ''));
        $batchId = $batchId !== '' ? Str::slug($batchId) : 'fake-'.Str::lower(Str::random(8));
        $dryRun = (bool) $this->option('dry-run');

        [$photoPool, $videoPool] = $this->mediaPools();

        if ($photoPool->isEmpty()) {
            $this->error('No image files found in public/fake-media.');

            return self::FAILURE;
        }

        $plannedPosts = $guestsToCreate * $postsPerGuest;
        $this->info("Event: {$event->id} {$event->name}");
        $this->info("Batch: {$batchId}");
        $this->info("Guests: {$guestsToCreate}");
        $this->info("Posts: {$plannedPosts}");
        $this->info('Photo pool: '.$photoPool->count());
        $this->info('Video pool: '.$videoPool->count());

        if ($dryRun) {
            $this->comment('Dry run only. No fake data written.');
            $this->line("Cleanup command: php artisan events:clear-fake-album {$event->id} {$batchId}");

            return self::SUCCESS;
        }

        $commentBodies = collect([
            'Love this moment.',
            'So beautiful.',
            'This is amazing.',
            'What a great memory.',
            'Absolutely stunning.',
            'Best day ever.',
            'This one is perfect.',
            'Such a lovely shot.',
            'Obsessed with this.',
            'Beautiful energy here.',
        ]);
        $messageBodies = collect([
            'So happy to celebrate with you both.',
            'An unforgettable night.',
            'Wishing you a lifetime of joy.',
            'Still smiling from this moment.',
            'What a beautiful celebration.',
            'Could not miss sharing this one.',
        ]);

        $disk = (string) config('events.upload_disk', 'public');
        $faker = fake('en_US');
        $now = now();

        try {
            DB::transaction(function () use (
                $event,
                $guestsToCreate,
                $postsPerGuest,
                $maxPhotos,
                $videoChance,
                $maxLikes,
                $maxComments,
                $batchId,
                $photoPool,
                $videoPool,
                $commentBodies,
                $messageBodies,
                $disk,
                $faker,
                $now
            ): void {
                $guests = collect();

                for ($guestIndex = 0; $guestIndex < $guestsToCreate; $guestIndex++) {
                    $guestCreatedAt = $now->copy()->subMinutes(($guestsToCreate - $guestIndex) * 4);
                    $guest = EventGuest::query()->forceCreate([
                        'event_id' => $event->id,
                        'guest_token' => sprintf('fake:%s:%s', $batchId, Str::uuid()->toString()),
                        'name' => $faker->unique()->name(),
                        'email' => $faker->unique()->safeEmail(),
                        'phone' => $faker->numerify('+40 7## ### ###'),
                        'guest_fields' => [
                            'seed_batch_id' => $batchId,
                            'seed_generated' => '1',
                        ],
                        'last_intent' => 'upload_media',
                        'last_seen_at' => $guestCreatedAt,
                        'created_at' => $guestCreatedAt,
                        'updated_at' => $guestCreatedAt,
                    ]);

                    $guests->push($guest);
                }

                $createdAssets = collect();

                foreach ($guests as $guestIndex => $guest) {
                    for ($postIndex = 0; $postIndex < $postsPerGuest; $postIndex++) {
                        $postCreatedAt = $now->copy()->subMinutes((($guestsToCreate - $guestIndex) * $postsPerGuest) + ($postsPerGuest - $postIndex));
                        $uploadBatchId = Str::uuid()->toString();
                        $photoCount = random_int(1, $maxPhotos);
                        $includeVideo = ! $videoPool->isEmpty() && random_int(1, 100) <= $videoChance;
                        $postMessage = random_int(1, 100) <= 45
                            ? (string) $messageBodies->random()
                            : null;

                        $postAssets = collect();

                        for ($photoIndex = 0; $photoIndex < $photoCount; $photoIndex++) {
                            $postAssets->push(
                                $this->createSeedAsset(
                                    event: $event,
                                    guest: $guest,
                                    batchId: $batchId,
                                    uploadBatchId: $uploadBatchId,
                                    uploadBatchIndex: $photoIndex,
                                    sourceFile: $photoPool->random(),
                                    kind: 'photo',
                                    disk: $disk,
                                    createdAt: $postCreatedAt,
                                    message: $postMessage,
                                ),
                            );
                        }

                        if ($includeVideo) {
                            $postAssets->push(
                                $this->createSeedAsset(
                                    event: $event,
                                    guest: $guest,
                                    batchId: $batchId,
                                    uploadBatchId: $uploadBatchId,
                                    uploadBatchIndex: $photoCount,
                                    sourceFile: $videoPool->random(),
                                    kind: 'video',
                                    disk: $disk,
                                    createdAt: $postCreatedAt,
                                    message: $postMessage,
                                ),
                            );
                        }

                        /** @var EventAsset|null $primaryAsset */
                        $primaryAsset = $postAssets
                            ->sortBy('metadata.upload_batch_index')
                            ->first();

                        if (! $primaryAsset instanceof EventAsset) {
                            continue;
                        }

                        $createdAssets = $createdAssets->concat($postAssets);
                        $likeableGuests = $guests
                            ->reject(fn (EventGuest $candidate): bool => $candidate->id === $guest->id)
                            ->shuffle()
                            ->take(min($maxLikes, max(0, $guests->count() - 1), random_int(0, max(0, $maxLikes))));

                        foreach ($likeableGuests as $likeGuest) {
                            EventAssetLike::query()->forceCreate([
                                'event_asset_id' => $primaryAsset->id,
                                'event_guest_id' => $likeGuest->id,
                                'created_at' => $postCreatedAt->copy()->addMinutes(random_int(1, 90)),
                                'updated_at' => $postCreatedAt->copy()->addMinutes(random_int(1, 90)),
                            ]);
                        }

                        $commentGuests = $guests
                            ->reject(fn (EventGuest $candidate): bool => $candidate->id === $guest->id)
                            ->shuffle()
                            ->take(min($maxComments, max(0, $guests->count() - 1), random_int(0, max(0, $maxComments))));

                        foreach ($commentGuests as $commentGuest) {
                            $commentCreatedAt = $postCreatedAt->copy()->addMinutes(random_int(2, 120));
                            $comment = EventAssetComment::query()->forceCreate([
                                'event_asset_id' => $primaryAsset->id,
                                'event_guest_id' => $commentGuest->id,
                                'body' => (string) $commentBodies->random(),
                                'created_at' => $commentCreatedAt,
                                'updated_at' => $commentCreatedAt,
                            ]);

                            $commentLikers = $guests
                                ->reject(fn (EventGuest $candidate): bool => in_array($candidate->id, [$guest->id, $commentGuest->id], true))
                                ->shuffle()
                                ->take(random_int(0, min(4, max(0, $guests->count() - 2))));

                            foreach ($commentLikers as $commentLiker) {
                                EventAssetCommentLike::query()->forceCreate([
                                    'event_asset_comment_id' => $comment->id,
                                    'event_guest_id' => $commentLiker->id,
                                    'created_at' => $commentCreatedAt->copy()->addMinutes(random_int(1, 45)),
                                    'updated_at' => $commentCreatedAt->copy()->addMinutes(random_int(1, 45)),
                                ]);
                            }
                        }
                    }
                }

                $this->recalculateEventCounters($event);
            });
        } catch (\Throwable $exception) {
            $this->cleanupWrittenFiles($disk);
            report($exception);
            $this->error('Fake album generation failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Fake album data created successfully.');
        $this->line("Cleanup command: php artisan events:clear-fake-album {$event->id} {$batchId}");

        return self::SUCCESS;
    }

    private function resolveEvent(string $eventReference): Event
    {
        return Event::query()
            ->where('id', is_numeric($eventReference) ? (int) $eventReference : -1)
            ->orWhere('share_token', $eventReference)
            ->firstOrFail();
    }

    /**
     * @return array{0: Collection<int, SplFileInfo>, 1: Collection<int, SplFileInfo>}
     */
    private function mediaPools(): array
    {
        $files = collect((new \FilesystemIterator(public_path('fake-media'))))
            ->filter(fn (SplFileInfo $file): bool => $file->isFile() && ! str_starts_with($file->getFilename(), '.'))
            ->values();

        $photoPool = $files
            ->filter(fn (SplFileInfo $file): bool => str_starts_with($this->mimeTypeForFile($file), 'image/'))
            ->values();
        $videoPool = $files
            ->filter(fn (SplFileInfo $file): bool => str_starts_with($this->mimeTypeForFile($file), 'video/'))
            ->values();

        return [$photoPool, $videoPool];
    }

    private function mimeTypeForFile(SplFileInfo $file): string
    {
        $mime = mime_content_type($file->getPathname());

        if (is_string($mime) && $mime !== '') {
            return $mime;
        }

        return (new HttpFile($file->getPathname()))->getMimeType() ?? 'application/octet-stream';
    }

    private function createSeedAsset(
        Event $event,
        EventGuest $guest,
        string $batchId,
        string $uploadBatchId,
        int $uploadBatchIndex,
        SplFileInfo $sourceFile,
        string $kind,
        string $disk,
        CarbonInterface $createdAt,
        ?string $message,
    ): EventAsset {
        $sourcePath = $sourceFile->getPathname();
        $extension = Str::lower($sourceFile->getExtension());
        $displayFilename = sprintf(
            '%s-%s-%02d%s',
            Str::slug($guest->name) ?: 'guest',
            $kind === 'video' ? 'video' : 'photo',
            $uploadBatchIndex + 1,
            $extension !== '' ? ".{$extension}" : '',
        );
        $storagePath = sprintf(
            'events/%d/fake-batches/%s/%d/%s-%s',
            $event->id,
            $batchId,
            $guest->id,
            Str::uuid()->toString(),
            $displayFilename,
        );

        $stream = fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException("Unable to read fake media file {$sourcePath}.");
        }

        Storage::disk($disk)->put($storagePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        $this->writtenPaths[] = $storagePath;

        [$width, $height] = $kind === 'photo'
            ? array_pad((array) @getimagesize($sourcePath), 2, null)
            : [null, null];

        $asset = EventAsset::query()->forceCreate([
            'event_id' => $event->id,
            'user_id' => null,
            'kind' => $kind,
            'disk' => $disk,
            'path' => $storagePath,
            'original_filename' => $displayFilename,
            'mime_type' => $this->mimeTypeForFile($sourceFile),
            'size_bytes' => (int) $sourceFile->getSize(),
            'width' => is_int($width) ? $width : null,
            'height' => is_int($height) ? $height : null,
            'duration_seconds' => $kind === 'video' ? random_int(10, min(25, max(10, (int) $event->video_max_duration_seconds))) : null,
            'moderation_status' => 'approved',
            'moderation_score' => null,
            'is_watermarked' => false,
            'metadata' => [
                'guest_token' => $guest->guest_token,
                'guest_name' => $guest->name,
                'guest_email' => $guest->email,
                'guest_phone' => $guest->phone,
                'message' => $message,
                'upload_batch_id' => $uploadBatchId,
                'upload_batch_index' => $uploadBatchIndex,
                'seed_batch_id' => $batchId,
                'seed_generated' => true,
            ],
            'reviewed_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        if ($kind === 'photo') {
            GenerateEventAssetImageVariants::dispatch($asset->id)->afterCommit();
        } elseif ($kind === 'video') {
            GenerateEventAssetVideoThumbnails::dispatch($asset->id)->afterCommit();
        }

        return $asset;
    }

    private function recalculateEventCounters(Event $event): void
    {
        $event->forceFill([
            'upload_count' => (int) EventAsset::query()->where('event_id', $event->id)->count(),
            'storage_used_bytes' => (int) (EventAsset::query()->where('event_id', $event->id)->sum('size_bytes') ?? 0),
        ])->save();
    }

    private function cleanupWrittenFiles(string $disk): void
    {
        foreach ($this->writtenPaths as $writtenPath) {
            Storage::disk($disk)->delete($writtenPath);
        }

        $this->writtenPaths = [];
    }
}
