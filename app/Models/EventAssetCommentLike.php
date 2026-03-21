<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssetCommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_asset_comment_id',
        'event_guest_id',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(EventAssetComment::class, 'event_asset_comment_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(EventGuest::class, 'event_guest_id');
    }
}
