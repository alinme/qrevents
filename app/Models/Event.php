<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_LIVE = 'live';

    public const STATUS_GRACE = 'grace';

    public const STATUS_LOCKED = 'locked';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'user_id',
        'plan_id',
        'type',
        'name',
        'venue_address',
        'event_date',
        'event_dates',
        'sub_events',
        'timezone',
        'attendee_estimate',
        'status',
        'onboarding_step',
        'onboarding_completed_at',
        'currency',
        'is_paid',
        'payment_due_at',
        'paid_at',
        'billing_note',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_paid_amount_cents',
        'stripe_paid_currency',
        'cleanup_review_state',
        'cleanup_reviewed_at',
        'retention_ends_at',
        'renewal_grace_ends_at',
        'upload_window_starts_at',
        'upload_window_ends_at',
        'grace_ends_at',
        'hard_lock_at',
        'storage_limit_bytes',
        'storage_used_bytes',
        'upload_limit',
        'upload_count',
        'upload_window_days',
        'customization_tier',
        'download_all_enabled',
        'moderation_tools_enabled',
        'remove_app_branding',
        'video_max_duration_seconds',
        'photo_max_size_bytes',
        'video_max_size_bytes',
        'album_public',
        'moderation_enabled',
        'auto_moderation_enabled',
        'branding',
        'share_token',
        'media_export_status',
        'media_export_token',
        'media_export_disk',
        'media_export_path',
        'media_export_requested_at',
        'media_export_started_at',
        'media_export_completed_at',
        'media_export_failed_at',
        'media_export_error',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'event_dates' => 'array',
            'sub_events' => 'array',
            'onboarding_completed_at' => 'datetime',
            'is_paid' => 'boolean',
            'attendee_estimate' => 'integer',
            'payment_due_at' => 'datetime',
            'paid_at' => 'datetime',
            'stripe_paid_amount_cents' => 'integer',
            'cleanup_reviewed_at' => 'datetime',
            'retention_ends_at' => 'datetime',
            'renewal_grace_ends_at' => 'datetime',
            'upload_window_starts_at' => 'datetime',
            'upload_window_ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'hard_lock_at' => 'datetime',
            'download_all_enabled' => 'boolean',
            'moderation_tools_enabled' => 'boolean',
            'remove_app_branding' => 'boolean',
            'album_public' => 'boolean',
            'moderation_enabled' => 'boolean',
            'auto_moderation_enabled' => 'boolean',
            'branding' => 'array',
            'media_export_requested_at' => 'datetime',
            'media_export_started_at' => 'datetime',
            'media_export_completed_at' => 'datetime',
            'media_export_failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(EventAsset::class);
    }

    public function collaborators(): HasMany
    {
        return $this->hasMany(EventCollaborator::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(EventGuest::class);
    }

    public function guestParties(): HasMany
    {
        return $this->hasMany(EventGuestParty::class);
    }
}
