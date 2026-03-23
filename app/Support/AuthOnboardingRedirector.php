<?php

namespace App\Support;

use App\Models\Event;
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

        if ($latestOwnedEvent instanceof Event && ! $this->hasCompletedOwnedEvent($user)) {
            return redirect()->to($this->onboardingStepPath($latestOwnedEvent));
        }

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

    private function hasCompletedOwnedEvent(User $user): bool
    {
        return $user->events()
            ->whereNotNull('onboarding_completed_at')
            ->exists();
    }

    private function hasActiveCollaboratorEvent(User $user): bool
    {
        return EventCollaborator::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'accepted'])
            ->exists();
    }

    private function onboardingStepPath(Event $event): string
    {
        return match ($event->onboarding_step) {
            'creating' => route('onboarding.creating', $event, absolute: false),
            'photos' => route('onboarding.photos', $event, absolute: false),
            default => route('onboarding.create', absolute: false),
        };
    }
}
