<?php

namespace App\Models;

use Database\Factories\EventGuestPartyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventGuestParty extends Model
{
    /** @use HasFactory<EventGuestPartyFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'phone',
        'invited_attendees_count',
        'confirmed_attendees_count',
        'attendance_status',
        'notes',
        'guest_names',
        'invitation_status',
        'invitation_delivery_channel',
        'invitation_delivered_at',
        'invitation_token',
        'invitation_open_count',
        'invitation_first_opened_at',
        'invitation_last_opened_at',
        'invitation_last_opened_ip',
        'invitation_last_opened_user_agent',
        'responded_at',
        'gift_type',
        'gift_currency',
        'gift_amount',
        'meal_preference',
        'response_notes',
        'response_ip_address',
        'response_user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'invited_attendees_count' => 'integer',
            'confirmed_attendees_count' => 'integer',
            'invitation_delivered_at' => 'datetime',
            'invitation_open_count' => 'integer',
            'invitation_first_opened_at' => 'datetime',
            'invitation_last_opened_at' => 'datetime',
            'responded_at' => 'datetime',
            'gift_amount' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function invitationViews(): HasMany
    {
        return $this->hasMany(EventGuestPartyInvitationView::class);
    }
}
