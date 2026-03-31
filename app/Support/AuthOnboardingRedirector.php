<?php

namespace App\Support;

use App\Models\EventCollaborator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthOnboardingRedirector
{
    public function dashboardRedirect(User $user): ?RedirectResponse
    {
        $latestOwnedEvent = $user->events()
            ->latest('id')
            ->first();

        if ($user->isBusinessAccount() && ! $user->hasCompletedBusinessOnboarding()) {
            return to_route('dashboard.business.onboarding');
        }

        if ($latestOwnedEvent === null && ! $this->hasActiveCollaboratorEvent($user)) {
            return $user->isBusinessAccount()
                ? to_route('dashboard.business')
                : to_route('onboarding.create');
        }

        return null;
    }

    public function fallbackPathFor(User $user): string
    {
        if ($user->canAccessAdmin()) {
            return route('admin.overview', absolute: false);
        }

        $dashboardRedirect = $this->dashboardRedirect($user);

        return $dashboardRedirect !== null
            ? $dashboardRedirect->getTargetUrl()
            : ($user->isBusinessAccount()
                ? route('dashboard.business', absolute: false)
                : route('dashboard', absolute: false));
    }

    private function hasActiveCollaboratorEvent(User $user): bool
    {
        return EventCollaborator::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'accepted'])
            ->exists();
    }
}
