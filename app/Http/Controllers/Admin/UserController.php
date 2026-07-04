<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SharesAdminNavigation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserAccountTypeRequest;
use App\Models\AdminAuditLog;
use App\Models\Event;
use App\Models\User;
use App\Support\EventDataPurger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    use SharesAdminNavigation;

    public function index(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        $search = trim((string) $request->string('search'));
        $type = (string) $request->string('type');
        $type = in_array($type, User::assignableAccountTypes(), true) ? $type : '';

        $users = User::query()
            ->withCount('events')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn ($query) => $query->where('account_type', $type))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/Users', [
            ...$this->adminPanelProps(),
            'users' => $this->userRows($users->getCollection()),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'total' => $users->total(),
                'prevPageUrl' => $users->previousPageUrl(),
                'nextPageUrl' => $users->nextPageUrl(),
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'accountTypes' => $this->accountTypeOptions(),
        ]);
    }

    public function show(Request $request, User $user): Response
    {
        $this->assertSuperAdmin($request);

        $events = $user->events()
            ->with('plan:id,name,currency,price_cents')
            ->withCount('assets')
            ->latest('id')
            ->limit(50)
            ->get();

        $auditEntries = AdminAuditLog::query()
            ->with('admin:id,name,email')
            ->where(function ($query) use ($user): void {
                $query
                    ->where(function ($inner) use ($user): void {
                        $inner->where('target_type', User::class)->where('target_id', $user->id);
                    })
                    ->orWhere('admin_user_id', $user->id);
            })
            ->latest('id')
            ->limit(25)
            ->get();

        return Inertia::render('admin/UserDetail', [
            ...$this->adminPanelProps(),
            'profile' => $this->userProfile($user),
            'events' => $events->map(fn (Event $event): array => [
                'id' => $event->id,
                'name' => $event->name,
                'status' => (string) $event->status,
                'planName' => $event->plan?->name ?? 'Custom plan',
                'isPaid' => (bool) $event->is_paid,
                'assetCount' => (int) ($event->assets_count ?? 0),
                'createdAt' => $event->created_at?->toIso8601String(),
                'links' => [
                    'settings' => route('events.settings', $event),
                ],
            ])->values()->all(),
            'auditEntries' => $this->auditRows($auditEntries),
            'accountTypes' => $this->accountTypeOptions(),
            'canImpersonate' => $this->canImpersonate($request->user(), $user),
            'canDelete' => $this->canDelete($request->user(), $user),
            'canChangeAccountType' => $request->user()->id !== $user->id,
            'links' => [
                'updateAccountType' => route('admin.users.account-type', $user),
                'verify' => route('admin.users.verify', $user),
                'impersonate' => route('admin.impersonate.start', $user),
                'destroy' => route('admin.users.destroy', $user),
            ],
        ]);
    }

    public function updateAccountType(UpdateUserAccountTypeRequest $request, User $user): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $admin = $request->user();
        abort_if($admin->id === $user->id, 403, 'You cannot change your own account type.');

        $newType = (string) $request->validated()['account_type'];
        $previousType = (string) $user->account_type;

        if ($newType === $previousType) {
            return back()->with('info', 'No change — account type is already set.');
        }

        // Prevent removing the final super admin from the platform.
        if ($previousType === User::ACCOUNT_TYPE_SUPER_ADMIN
            && $newType !== User::ACCOUNT_TYPE_SUPER_ADMIN
            && $this->superAdminCount() <= 1) {
            return back()->with('error', 'You cannot demote the last super admin.');
        }

        $user->forceFill(['account_type' => $newType])->save();

        AdminAuditLog::record(
            $admin,
            'user.account_type.updated',
            $user,
            $user->email,
            ['from' => $previousType, 'to' => $newType],
            $request->ip(),
        );

        return back()->with('success', "{$user->name} is now a ".$this->accountTypeLabel($newType).'.');
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        if ($user->email_verified_at !== null) {
            return back()->with('info', 'That account is already verified.');
        }

        $user->forceFill(['email_verified_at' => now()])->save();

        AdminAuditLog::record(
            $request->user(),
            'user.email.verified',
            $user,
            $user->email,
            [],
            $request->ip(),
        );

        return back()->with('success', "{$user->email} marked as verified.");
    }

    public function destroy(Request $request, User $user, EventDataPurger $purger): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $admin = $request->user();

        if (! $this->canDelete($admin, $user)) {
            return back()->with('error', 'That account cannot be deleted.');
        }

        $email = $user->email;
        $name = $user->name;

        DB::transaction(function () use ($user, $purger): void {
            // Purge each owned event and its stored media before removing the user.
            $user->events()->withTrashed()->get()->each(function (Event $event) use ($purger): void {
                $purger->purgeEventForDeletion($event);
            });

            $user->delete();
        });

        AdminAuditLog::record(
            $admin,
            'user.deleted',
            null,
            $email,
            ['deleted_user_id' => $user->id, 'name' => $name],
            $request->ip(),
        );

        return redirect()->route('admin.users')->with('success', "{$email} and their events were deleted.");
    }

    private function canImpersonate(User $admin, User $user): bool
    {
        return $admin->id !== $user->id && $user->account_type !== User::ACCOUNT_TYPE_SUPER_ADMIN;
    }

    private function canDelete(User $admin, User $user): bool
    {
        if ($admin->id === $user->id) {
            return false;
        }

        if ($user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN && $this->superAdminCount() <= 1) {
            return false;
        }

        return true;
    }

    private function superAdminCount(): int
    {
        return User::query()->where('account_type', User::ACCOUNT_TYPE_SUPER_ADMIN)->count();
    }

    /**
     * @param  Collection<int, User>  $users
     * @return list<array<string, mixed>>
     */
    private function userRows(Collection $users): array
    {
        return $users->map(fn (User $user): array => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'accountType' => (string) $user->account_type,
            'accountTypeLabel' => $this->accountTypeLabel((string) $user->account_type),
            'accountTypeTone' => $this->accountTypeTone((string) $user->account_type),
            'eventCount' => (int) ($user->events_count ?? 0),
            'isVerified' => $user->email_verified_at !== null,
            'twoFactorEnabled' => $user->two_factor_secret !== null,
            'createdAt' => $user->created_at?->toIso8601String(),
            'links' => [
                'show' => route('admin.users.show', $user),
            ],
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function userProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'accountType' => (string) $user->account_type,
            'accountTypeLabel' => $this->accountTypeLabel((string) $user->account_type),
            'accountTypeTone' => $this->accountTypeTone((string) $user->account_type),
            'isVerified' => $user->email_verified_at !== null,
            'verifiedAt' => $user->email_verified_at?->toIso8601String(),
            'twoFactorEnabled' => $user->two_factor_secret !== null,
            'hasGoogle' => filled($user->google_id),
            'eventCount' => $user->events()->count(),
            'businessWalletCredits' => (int) ($user->business_wallet_credits ?? 0),
            'createdAt' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, AdminAuditLog>  $entries
     * @return list<array<string, mixed>>
     */
    private function auditRows(Collection $entries): array
    {
        return $entries->map(fn (AdminAuditLog $entry): array => [
            'id' => $entry->id,
            'action' => $entry->action,
            'adminName' => $entry->admin?->name ?? 'System',
            'targetLabel' => $entry->target_label,
            'meta' => $entry->meta,
            'createdAt' => $entry->created_at?->toIso8601String(),
        ])->values()->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function accountTypeOptions(): array
    {
        return collect(User::assignableAccountTypes())
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => $this->accountTypeLabel($type),
            ])
            ->all();
    }

    private function accountTypeLabel(string $type): string
    {
        return match ($type) {
            User::ACCOUNT_TYPE_SUPER_ADMIN => 'Super admin',
            User::ACCOUNT_TYPE_BUSINESS => 'Business',
            default => 'User',
        };
    }

    private function accountTypeTone(string $type): string
    {
        return match ($type) {
            User::ACCOUNT_TYPE_SUPER_ADMIN => 'rose',
            User::ACCOUNT_TYPE_BUSINESS => 'violet',
            default => 'zinc',
        };
    }
}
