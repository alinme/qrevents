<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IsgdShortUrlManager
{
    /**
     * @return array{albumShortUrl: string|null, wallShortUrl: string|null}
     */
    public function forEvent(Event $event): array
    {
        $albumShortUrl = $this->ensureShortUrl($event, 'album');
        $wallShortUrl = $this->ensureShortUrl($event, 'wall');

        return [
            'albumShortUrl' => $albumShortUrl,
            'wallShortUrl' => $wallShortUrl,
        ];
    }

    public function candidateSlug(Event $event, string $type): string
    {
        $prefix = $type === 'wall' ? 'w' : 'a';

        return $prefix.Str::lower($event->publicAlbumCode());
    }

    public function candidateUrl(Event $event, string $type): string
    {
        return rtrim((string) config('services.isgd.base_url'), '/').'/'.$this->candidateSlug($event, $type);
    }

    private function ensureShortUrl(Event $event, string $type): ?string
    {
        $field = $type === 'wall' ? 'wall_short_url' : 'album_short_url';
        $existingValue = $event->{$field};

        if (is_string($existingValue) && trim($existingValue) !== '') {
            return $existingValue;
        }

        $candidateUrl = $this->candidateUrl($event, $type);

        if (app()->runningUnitTests()) {
            $event->forceFill([$field => $candidateUrl])->saveQuietly();

            return $candidateUrl;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->get(rtrim((string) config('services.isgd.base_url'), '/').'/create.php', [
                    'format' => 'json',
                    'url' => $this->targetUrl($event, $type),
                    'shorturl' => $this->candidateSlug($event, $type),
                ]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to reach is.gd while creating a short URL.', [
                'event_id' => $event->id,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Failed to create is.gd short URL.', [
                'event_id' => $event->id,
                'type' => $type,
                'status' => $response->status(),
                'body' => Str::limit((string) $response->body(), 240),
            ]);

            return null;
        }

        $shortUrl = $response->json('shorturl');

        if (! is_string($shortUrl) || trim($shortUrl) === '') {
            Log::warning('is.gd did not return a short URL.', [
                'event_id' => $event->id,
                'type' => $type,
                'body' => $response->json(),
            ]);

            return null;
        }

        $event->forceFill([$field => $shortUrl])->saveQuietly();

        return $shortUrl;
    }

    private function targetUrl(Event $event, string $type): string
    {
        return route($type === 'wall' ? 'events.wall' : 'events.album', $event->publicAlbumCode());
    }
}
