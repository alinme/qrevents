<?php

namespace App\Http\Middleware;

use App\Models\Event;
use App\Support\FrontendLocalization;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $currentLocale = $this->resolveLocale($request);
        app()->setLocale($currentLocale);

        if ($user !== null) {
            $user->syncConfiguredAccountType();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user?->authContext(),
            ],
            'accountNavigation' => $this->sharedAccountNavigation($request),
            'businessNavigation' => $this->sharedBusinessNavigation($request),
            'sidebarLabel' => $this->sharedSidebarLabel($request),
            'flash' => [
                'success' => fn (): mixed => $request->session()->get('success'),
                'error' => fn (): mixed => $request->session()->get('error'),
                'info' => fn (): mixed => $request->session()->get('info'),
            ],
            'locale' => [
                'current' => $currentLocale,
                'available' => FrontendLocalization::supportedLocales(),
            ],
            'translations' => FrontendLocalization::translationsFor($currentLocale),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return list<array{title: string, href: string}>
     */
    private function sharedAccountNavigation(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [];
        }

        if ($user->canAccessAdmin()) {
            return [
                [
                    'title' => 'Admin',
                    'href' => route('admin.overview'),
                ],
            ];
        }

        if ($user->canAccessBusinessDashboard()) {
            return [
                [
                    'title' => 'Events',
                    'href' => route('dashboard.account'),
                ],
                [
                    'title' => 'Business',
                    'href' => route('dashboard.business'),
                ],
            ];
        }

        return [
            [
                'title' => 'Events',
                'href' => route('dashboard'),
            ],
        ];
    }

    /**
     * @return list<array{title: string, href: string}>
     */
    private function sharedBusinessNavigation(Request $request): array
    {
        $user = $request->user();

        if ($user === null || ! $user->canAccessBusinessDashboard()) {
            return [];
        }

        return [
            [
                'title' => 'Business',
                'href' => route('dashboard.business'),
            ],
            [
                'title' => 'Billing',
                'href' => route('dashboard.business.wallet.history'),
            ],
            [
                'title' => 'Events',
                'href' => route('dashboard.business.events.index'),
            ],
        ];
    }

    private function sharedSidebarLabel(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        if ($user->canAccessAdmin()) {
            return 'Admin';
        }

        return 'Account';
    }

    private function resolveLocale(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if (in_array($routeName, ['events.album', 'events.wall'], true)) {
            $shareToken = $request->route('shareToken');

            if (is_string($shareToken) && $shareToken !== '') {
                $event = Event::query()
                    ->select(['id', 'branding'])
                    ->where('share_token', $shareToken)
                    ->first();

                $branding = is_array($event?->branding) ? $event->branding : [];

                return FrontendLocalization::resolveEventLocale($request, $branding);
            }
        }

        return FrontendLocalization::resolveSiteLocale($request);
    }
}
