<?php

namespace App\Models;

use Database\Factories\EventGuestPartyInvitationActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGuestPartyInvitationActivity extends Model
{
    /** @use HasFactory<EventGuestPartyInvitationActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_guest_party_id',
        'actor_user_id',
        'activity_type',
        'delivery_channel',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guestParty(): BelongsTo
    {
        return $this->belongsTo(EventGuestParty::class, 'event_guest_party_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
