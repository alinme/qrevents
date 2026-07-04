<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public const SESSION_KEY = 'impersonator_id';

    public function start(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);

        $admin = $request->user();

        abort_if($admin->id === $user->id, 403, 'You cannot impersonate yourself.');
        abort_if($user->account_type === User::ACCOUNT_TYPE_SUPER_ADMIN, 403, 'Super admins cannot be impersonated.');
        abort_if($request->session()->has(self::SESSION_KEY), 409, 'Already impersonating a user.');

        AdminAuditLog::record(
            $admin,
            'user.impersonation.started',
            $user,
            $user->email,
            [],
            $request->ip(),
        );

        Auth::login($user);
        $request->session()->put(self::SESSION_KEY, $admin->id);

        return redirect()->route('dashboard')
            ->with('info', "You are now viewing the app as {$user->email}.");
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull(self::SESSION_KEY);

        if ($impersonatorId === null) {
            return redirect()->route('dashboard');
        }

        $impersonatedUser = $request->user();
        $admin = User::query()->find($impersonatorId);

        if ($admin === null || ! $admin->canAccessAdmin()) {
            Auth::logout();

            return redirect()->route('login');
        }

        Auth::login($admin);

        AdminAuditLog::record(
            $admin,
            'user.impersonation.stopped',
            $impersonatedUser,
            $impersonatedUser?->email,
            [],
            $request->ip(),
        );

        $target = $impersonatedUser instanceof User
            ? redirect()->route('admin.users.show', $impersonatedUser)
            : redirect()->route('admin.users');

        return $target->with('success', 'Returned to your admin account.');
    }
}
