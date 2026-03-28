<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessWalletPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credits_purchased',
        'bonus_credits',
        'total_credits',
        'base_amount_cents',
        'checkout_currency',
        'localized_amount_cents',
        'locked_fx_rate',
        'status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'locked_fx_rate' => 'decimal:8',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BusinessWalletTransaction::class, 'purchase_id');
    }
}
