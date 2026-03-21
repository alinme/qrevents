<?php

namespace App\Models;

use Database\Factories\EventAssetLikeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssetLike extends Model
{
    /** @use HasFactory<EventAssetLikeFactory> */
    use HasFactory;

    protected $fillable = [
        'event_asset_id',
        'event_guest_id',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(EventAsset::class, 'event_asset_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(EventGuest::class, 'event_guest_id');
    }
}
