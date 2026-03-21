<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public const ACCOUNT_TYPE_USER = 'user';

    public const ACCOUNT_TYPE_BUSINESS = 'business';

    public const ACCOUNT_TYPE_SUPER_ADMIN = 'super_admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_type',
        'google_id',
        'google_avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_type' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function eventCollaborations(): HasMany
    {
        return $this->hasMany(EventCollaborator::class);
    }

    public function accountType(): string
    {
        $this->syncConfiguredAccountType();

        $accountType = trim((string) $this->account_type);

        return in_array($accountType, [
            self::ACCOUNT_TYPE_USER,
            self::ACCOUNT_TYPE_BUSINESS,
            self::ACCOUNT_TYPE_SUPER_ADMIN,
        ], true)
            ? $accountType
            : self::ACCOUNT_TYPE_USER;
    }

    public function accountTypeLabel(): string
    {
        return match ($this->accountType()) {
            self::ACCOUNT_TYPE_SUPER_ADMIN => 'Super admin',
            self::ACCOUNT_TYPE_BUSINESS => 'Business',
            default => 'User',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this->accountType() === self::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    public function isBusinessAccount(): bool
    {
        return in_array($this->accountType(), [
            self::ACCOUNT_TYPE_BUSINESS,
            self::ACCOUNT_TYPE_SUPER_ADMIN,
        ], true);
    }

    public function canAccessAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canAccessBusinessDashboard(): bool
    {
        return $this->isBusinessAccount();
    }

    public function promoteToBusiness(): void
    {
        if ($this->hasConfiguredSuperAdminEmail()) {
            return;
        }

        if (! $this->ownsMultipleEvents()) {
            return;
        }

        if ($this->account_type === self::ACCOUNT_TYPE_BUSINESS) {
            return;
        }

        $this->forceFill([
            'account_type' => self::ACCOUNT_TYPE_BUSINESS,
        ])->save();
    }

    public function promoteToSuperAdmin(): void
    {
        if ($this->accountType() === self::ACCOUNT_TYPE_SUPER_ADMIN) {
            return;
        }

        $this->forceFill([
            'account_type' => self::ACCOUNT_TYPE_SUPER_ADMIN,
        ])->save();
    }

    public function syncConfiguredAccountType(): void
    {
        if ($this->hasConfiguredSuperAdminEmail()) {
            if ($this->account_type === self::ACCOUNT_TYPE_SUPER_ADMIN) {
                return;
            }

            $this->forceFill([
                'account_type' => self::ACCOUNT_TYPE_SUPER_ADMIN,
            ])->save();

            return;
        }

        $expectedAccountType = $this->ownsMultipleEvents()
            ? self::ACCOUNT_TYPE_BUSINESS
            : self::ACCOUNT_TYPE_USER;

        if ($this->account_type === $expectedAccountType) {
            return;
        }

        $this->forceFill([
            'account_type' => $expectedAccountType,
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    public function authContext(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->google_avatar,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'accountType' => $this->accountType(),
            'accountLabel' => $this->accountTypeLabel(),
            'capabilities' => [
                'accessAdmin' => $this->canAccessAdmin(),
                'accessBusinessDashboard' => $this->canAccessBusinessDashboard(),
            ],
        ];
    }

    private function hasConfiguredSuperAdminEmail(): bool
    {
        $email = Str::lower(trim((string) $this->email));

        if ($email === '') {
            return false;
        }

        /** @var array<int, string> $superAdminEmails */
        $superAdminEmails = config('app.super_admin_emails', []);

        return in_array($email, $superAdminEmails, true);
    }

    private function ownsMultipleEvents(): bool
    {
        return $this->events()->count() >= 2;
    }
}
