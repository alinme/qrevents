<?php

namespace App\Support;

use App\Models\EventCollaborator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AuthOnboardingRedirector
{
    public function dashboardRedirect(User $user): ?RedirectResponse
    {
        if ($user->canAccessAdmin()) {
            return null;
        }

        $latestOwnedEvent = $user->events()
            ->latest('id')
            ->first();

        if ($latestOwnedEvent === null && ! $this->hasActiveCollaboratorEvent($user)) {
            return to_route('onboarding.create');
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
            : route('dashboard', absolute: false);
    }

    private function hasActiveCollaboratorEvent(User $user): bool
    {
        return EventCollaborator::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'accepted'])
            ->exists();
    }
}
