<?php

namespace App\Models;

use Database\Factories\EventGuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventGuest extends Model
{
    /** @use HasFactory<EventGuestFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'guest_token',
        'name',
        'email',
        'phone',
        'avatar_disk',
        'avatar_path',
        'guest_fields',
        'last_intent',
        'last_seen_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'guest_fields' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(EventAssetLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(EventAssetComment::class);
    }

    public function commentLikes(): HasMany
    {
        return $this->hasMany(EventAssetCommentLike::class);
    }
}
