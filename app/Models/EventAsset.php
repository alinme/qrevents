<?php

namespace App\Models;

use Database\Factories\EventAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventAsset extends Model
{
    /** @use HasFactory<EventAssetFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'kind',
        'disk',
        'path',
        'thumbnail_path',
        'preview_path',
        'watermarked_thumbnail_path',
        'watermarked_preview_path',
        'watermarked_download_path',
        'video_thumbnail_path',
        'watermarked_video_thumbnail_path',
        'video_preview_path',
        'watermarked_video_preview_path',
        'watermarked_video_download_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'moderation_status',
        'moderation_score',
        'is_watermarked',
        'metadata',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'reviewed_at' => 'datetime',
            'is_watermarked' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(EventAssetLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventAssetComment::class);
    }
}
