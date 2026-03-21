<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventAssetComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_asset_id',
        'event_guest_id',
        'body',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(EventAsset::class, 'event_asset_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(EventGuest::class, 'event_guest_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(EventAssetCommentLike::class, 'event_asset_comment_id');
    }
}
