<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'admin_user_id',
        'action',
        'target_type',
        'target_id',
        'target_label',
        'meta',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Record an administrative action for later auditing.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function record(
        ?User $admin,
        string $action,
        ?Model $target = null,
        ?string $targetLabel = null,
        array $meta = [],
        ?string $ipAddress = null,
    ): self {
        return self::query()->create([
            'admin_user_id' => $admin?->id,
            'action' => $action,
            'target_type' => $target !== null ? $target::class : null,
            'target_id' => $target?->getKey(),
            'target_label' => $targetLabel,
            'meta' => $meta === [] ? null : $meta,
            'ip_address' => $ipAddress,
        ]);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
