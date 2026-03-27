<?php

namespace App\Models;

use Database\Factories\EventTableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTable extends Model
{
    /** @use HasFactory<EventTableFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'seats_count',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seats_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function guestParties(): HasMany
    {
        return $this->hasMany(EventGuestParty::class);
    }
}
