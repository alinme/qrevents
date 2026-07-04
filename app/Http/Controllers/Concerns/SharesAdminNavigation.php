<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait SharesAdminNavigation
{
    protected function assertSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->canAccessAdmin(), 403);
    }

    /**
     * Shared props (navigation, labels) for every admin panel page.
     *
     * @return array<string, mixed>
     */
    protected function adminPanelProps(): array
    {
        return [
            'adminNavigation' => [
                [
                    'title' => __('app.nav.overview'),
                    'href' => route('admin.overview'),
                    'icon' => 'overview',
                ],
                [
                    'title' => __('app.nav.users'),
                    'href' => route('admin.users'),
                    'icon' => 'users',
                ],
                [
                    'title' => __('app.nav.events'),
                    'href' => route('admin.events'),
                    'icon' => 'events',
                ],
                [
                    'title' => __('app.nav.plans'),
                    'href' => route('admin.plans'),
                    'icon' => 'plans',
                ],
                [
                    'title' => __('app.nav.audit_log'),
                    'href' => route('admin.audit'),
                    'icon' => 'audit',
                ],
                [
                    'title' => __('app.nav.settings'),
                    'href' => route('admin.settings.general'),
                    'icon' => 'settings',
                ],
            ],
            'sidebarLabel' => 'Admin',
        ];
    }
}
