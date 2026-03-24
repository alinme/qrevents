<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGuestPartyInvitationView extends Model
{
    /** @use HasFactory<\Database\Factories\EventGuestPartyInvitationViewFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_guest_party_id',
        'invitation_kind',
        'ip_address',
        'user_agent',
        'opened_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
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
}
