<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'currency',
        'price_cents',
        'storage_limit_bytes',
        'upload_limit',
        'retention_days',
        'grace_days',
        'upload_window_days',
        'customization_tier',
        'download_all_enabled',
        'moderation_tools_enabled',
        'remove_app_branding',
        'video_max_duration_seconds',
        'photo_max_size_bytes',
        'video_max_size_bytes',
        'is_active',
        'is_default',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'download_all_enabled' => 'boolean',
            'moderation_tools_enabled' => 'boolean',
            'remove_app_branding' => 'boolean',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
