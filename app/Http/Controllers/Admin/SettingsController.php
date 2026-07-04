<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\SharesAdminNavigation;
use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Support\DevToolRunner;
use App\Support\HealthMonitor;
use App\Support\SettingsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class SettingsController extends Controller
{
    use SharesAdminNavigation;

    public function __construct(private readonly SettingsRepository $settings) {}

    // ---- General -------------------------------------------------------

    public function general(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return $this->render('admin/settings/General', 'general', [
            'values' => [
                'site_name' => (string) $this->settings->get('general.site_name', config('app.name')),
                'company_name' => (string) $this->settings->get('general.company_name', ''),
                'support_phone' => (string) $this->settings->get('general.support_phone', ''),
                'public_email' => (string) $this->settings->get('general.public_email', ''),
                'business_address' => (string) $this->settings->get('general.business_address', ''),
            ],
            'logoUrl' => $this->fileUrl('general.logo_path'),
            'faviconUrl' => $this->fileUrl('general.favicon_path'),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:160'],
            'support_phone' => ['nullable', 'string', 'max:40'],
            'public_email' => ['nullable', 'email', 'max:160'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:1024'],
        ]);

        $this->settings->setMany('general', [
            'general.site_name' => $validated['site_name'],
            'general.company_name' => $validated['company_name'] ?? null,
            'general.support_phone' => $validated['support_phone'] ?? null,
            'general.public_email' => $validated['public_email'] ?? null,
            'general.business_address' => $validated['business_address'] ?? null,
        ]);

        $brandingChanged = $request->hasFile('logo') || $request->hasFile('favicon');

        if ($request->hasFile('logo')) {
            $this->settings->set('general.logo_path', $request->file('logo')->store('settings/branding', 'public'), 'general');
        }

        if ($request->hasFile('favicon')) {
            $this->settings->set('general.favicon_path', $request->file('favicon')->store('settings/branding', 'public'), 'general');
        }

        AdminAuditLog::record($request->user(), 'settings.general.updated', null, 'general', [], $request->ip());

        $redirect = back()->with('success', 'General settings saved.');

        if ($brandingChanged) {
            $redirect->with('info', 'Logo or favicon changed — run "Regenerate PWA icons" in DevTools so installed apps pick up the new icon.');
        }

        return $redirect;
    }

    // ---- Email / SMTP --------------------------------------------------

    public function email(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return $this->render('admin/settings/Email', 'email', [
            'values' => [
                'host' => (string) $this->settings->get('mail.host', ''),
                'port' => (int) $this->settings->get('mail.port', 587),
                'encryption' => (string) $this->settings->get('mail.encryption', 'tls'),
                'username' => (string) $this->settings->get('mail.username', ''),
                'from_address' => (string) $this->settings->get('mail.from_address', config('mail.from.address')),
                'from_name' => (string) $this->settings->get('mail.from_name', config('mail.from.name')),
            ],
            'passwordSet' => $this->settings->isFilled('mail.password'),
            'testRecipient' => (string) $request->user()->email,
            'testUrl' => route('admin.settings.email.test'),
        ]);
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $validated = $request->validate([
            'host' => ['nullable', 'string', 'max:180'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:180'],
            'password' => ['nullable', 'string', 'max:400'],
            'from_address' => ['nullable', 'email', 'max:180'],
            'from_name' => ['nullable', 'string', 'max:120'],
        ]);

        $this->settings->setMany('email', [
            'mail.host' => $validated['host'] ?? null,
            'mail.port' => $validated['port'] ?? 587,
            'mail.encryption' => $validated['encryption'] ?? 'tls',
            'mail.username' => $validated['username'] ?? null,
            'mail.password' => $validated['password'] ?? null,
            'mail.from_address' => $validated['from_address'] ?? null,
            'mail.from_name' => $validated['from_name'] ?? null,
        ], encryptedKeys: ['mail.password']);

        return $this->saved($request, 'email', 'Email settings saved.');
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $recipient = (string) $request->user()->email;

        try {
            Mail::raw(
                "This is a test email from EventSmart, sent to verify your SMTP settings.\n\nIf you received this, outgoing mail is working.",
                function ($message) use ($recipient): void {
                    $message->to($recipient)->subject('EventSmart · SMTP test email');
                },
            );
        } catch (Throwable $e) {
            return back()->with('error', 'Test email failed: '.$e->getMessage());
        }

        AdminAuditLog::record($request->user(), 'settings.email.tested', null, $recipient, [], $request->ip());

        return back()->with('success', "Test email sent to {$recipient}.");
    }

    // ---- Localization / SEO -------------------------------------------

    public function seo(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        $locales = ['en', 'ro', 'el'];
        $values = [];
        foreach ($locales as $locale) {
            $values[$locale] = [
                'title' => (string) $this->settings->get("seo.title.{$locale}", ''),
                'description' => (string) $this->settings->get("seo.description.{$locale}", ''),
            ];
        }

        return $this->render('admin/settings/Seo', 'seo', [
            'values' => $values,
            'locales' => $locales,
            'social' => [
                'facebook' => (string) $this->settings->get('social.facebook', ''),
                'instagram' => (string) $this->settings->get('social.instagram', ''),
                'linkedin' => (string) $this->settings->get('social.linkedin', ''),
                'twitter' => (string) $this->settings->get('social.twitter', ''),
                'youtube' => (string) $this->settings->get('social.youtube', ''),
                'tiktok' => (string) $this->settings->get('social.tiktok', ''),
            ],
            'shareImageUrl' => $this->fileUrl('seo.share_image'),
        ]);
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $validated = $request->validate([
            'values' => ['required', 'array'],
            'values.*.title' => ['nullable', 'string', 'max:180'],
            'values.*.description' => ['nullable', 'string', 'max:320'],
            'social' => ['array'],
            'social.*' => ['nullable', 'url', 'max:255'],
            'share_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $payload = [];
        foreach (['en', 'ro', 'el'] as $locale) {
            $payload["seo.title.{$locale}"] = $validated['values'][$locale]['title'] ?? null;
            $payload["seo.description.{$locale}"] = $validated['values'][$locale]['description'] ?? null;
        }
        $this->settings->setMany('seo', $payload);

        $social = $validated['social'] ?? [];
        $this->settings->setMany('integrations', [
            'social.facebook' => $social['facebook'] ?? null,
            'social.instagram' => $social['instagram'] ?? null,
            'social.linkedin' => $social['linkedin'] ?? null,
            'social.twitter' => $social['twitter'] ?? null,
            'social.youtube' => $social['youtube'] ?? null,
            'social.tiktok' => $social['tiktok'] ?? null,
        ]);

        if ($request->hasFile('share_image')) {
            $this->settings->set('seo.share_image', $request->file('share_image')->store('settings/seo', 'public'), 'seo');
        }

        return $this->saved($request, 'seo', 'Localization & SEO saved.');
    }

    // ---- Integrations / Storage ---------------------------------------

    public function integrations(Request $request): Response
    {
        $this->assertSuperAdmin($request);

        return $this->render('admin/settings/Integrations', 'integrations', [
            'storage' => [
                'access_key' => (string) $this->settings->get('storage.access_key', ''),
                'region' => (string) $this->settings->get('storage.region', ''),
                'bucket' => (string) $this->settings->get('storage.bucket', ''),
                'endpoint' => (string) $this->settings->get('storage.endpoint', ''),
            ],
            'storageSecretSet' => $this->settings->isFilled('storage.secret'),
        ]);
    }

    public function updateIntegrations(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $validated = $request->validate([
            'storage' => ['array'],
            'storage.access_key' => ['nullable', 'string', 'max:255'],
            'storage.secret' => ['nullable', 'string', 'max:400'],
            'storage.region' => ['nullable', 'string', 'max:60'],
            'storage.bucket' => ['nullable', 'string', 'max:120'],
            'storage.endpoint' => ['nullable', 'url', 'max:255'],
        ]);

        $storage = $validated['storage'] ?? [];
        $this->settings->setMany('integrations', [
            'storage.access_key' => $storage['access_key'] ?? null,
            'storage.secret' => $storage['secret'] ?? null,
            'storage.region' => $storage['region'] ?? null,
            'storage.bucket' => $storage['bucket'] ?? null,
            'storage.endpoint' => $storage['endpoint'] ?? null,
        ], encryptedKeys: ['storage.secret']);

        return $this->saved($request, 'integrations', 'Integrations saved.');
    }

    // ---- System Health -------------------------------------------------

    public function health(Request $request, HealthMonitor $monitor): Response
    {
        $this->assertSuperAdmin($request);

        return $this->render('admin/settings/Health', 'health', [
            'snapshot' => $monitor->snapshot(),
            'dataUrl' => route('admin.settings.health.data'),
            'links' => [
                'retryFailed' => route('admin.settings.health.retry-failed'),
                'flushFailed' => route('admin.settings.health.flush-failed'),
            ],
        ]);
    }

    public function healthData(Request $request, HealthMonitor $monitor): JsonResponse
    {
        $this->assertSuperAdmin($request);

        return response()->json($monitor->snapshot());
    }

    public function retryFailedJobs(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        Artisan::call('queue:retry', ['id' => ['all']]);
        AdminAuditLog::record($request->user(), 'settings.health.retry_failed', null, null, [], $request->ip());

        return back()->with('success', 'Queued all failed jobs for retry.');
    }

    public function flushFailedJobs(Request $request): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        Artisan::call('queue:flush');
        AdminAuditLog::record($request->user(), 'settings.health.flush_failed', null, null, [], $request->ip());

        return back()->with('success', 'Cleared the failed jobs list.');
    }

    // ---- DevTools ------------------------------------------------------

    public function devtools(Request $request, DevToolRunner $runner): Response
    {
        $this->assertSuperAdmin($request);

        $operations = collect($runner->operations())
            ->map(fn (array $meta, string $key): array => [
                'key' => $key,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'danger' => $meta['danger'],
            ])
            ->values()
            ->all();

        return $this->render('admin/settings/DevTools', 'devtools', [
            'operations' => $operations,
            'runUrl' => route('admin.settings.devtools.run'),
            'backupUrl' => route('admin.settings.devtools.backup'),
            'lastResult' => $request->session()->get('devtoolsResult'),
        ]);
    }

    public function runDevtool(Request $request, DevToolRunner $runner): RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $validated = $request->validate([
            'operation' => ['required', 'string', Rule::in(array_keys($runner->operations()))],
        ]);

        $operation = (string) $validated['operation'];
        $result = $runner->run($operation);

        AdminAuditLog::record(
            $request->user(),
            'settings.devtools.run',
            null,
            $operation,
            ['ok' => $result['ok']],
            $request->ip(),
        );

        return back()->with('devtoolsResult', [
            'operation' => $operation,
            'ok' => $result['ok'],
            'output' => $result['output'],
        ]);
    }

    public function backupSql(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->assertSuperAdmin($request);

        $conn = (array) config('database.connections.'.config('database.default'));

        if (($conn['driver'] ?? null) !== 'mysql') {
            return back()->with('error', 'SQL backup is only available for MySQL databases.');
        }

        $dir = storage_path('app/private/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $file = $dir.'/eventsmart-'.now()->format('Ymd-His').'.sql';

        $result = Process::timeout(300)
            ->env(['MYSQL_PWD' => (string) ($conn['password'] ?? '')])
            ->run([
                'mysqldump', '--single-transaction', '--no-tablespaces',
                '-h', (string) ($conn['host'] ?? '127.0.0.1'),
                '-P', (string) ($conn['port'] ?? 3306),
                '-u', (string) ($conn['username'] ?? ''),
                (string) ($conn['database'] ?? ''),
            ]);

        if (! $result->successful()) {
            return back()->with('error', 'Backup failed: '.trim($result->errorOutput() ?: 'mysqldump error'));
        }

        file_put_contents($file, $result->output());

        AdminAuditLog::record($request->user(), 'settings.devtools.sql_backup', null, basename($file), [], $request->ip());

        return response()->download($file, basename($file), ['Content-Type' => 'application/sql'])
            ->deleteFileAfterSend();
    }

    // ---- Shared helpers ------------------------------------------------

    /**
     * @param  array<string, mixed>  $props
     */
    private function render(string $component, string $activeTab, array $props): Response
    {
        return Inertia::render($component, [
            ...$this->adminPanelProps(),
            'settingsTabs' => $this->settingsTabs(),
            'activeTab' => $activeTab,
            ...$props,
        ]);
    }

    private function saved(Request $request, string $group, string $message): RedirectResponse
    {
        AdminAuditLog::record($request->user(), "settings.{$group}.updated", null, $group, [], $request->ip());

        return back()->with('success', $message);
    }

    private function fileUrl(string $key): ?string
    {
        $path = $this->settings->get($key);

        return is_string($path) && $path !== '' ? Storage::disk('public')->url($path) : null;
    }

    /**
     * @return list<array{key: string, title: string, href: string}>
     */
    private function settingsTabs(): array
    {
        return [
            ['key' => 'general', 'title' => 'General', 'href' => route('admin.settings.general')],
            ['key' => 'email', 'title' => 'Email & SMTP', 'href' => route('admin.settings.email')],
            ['key' => 'seo', 'title' => 'Localization & SEO', 'href' => route('admin.settings.seo')],
            ['key' => 'integrations', 'title' => 'Integrations & Storage', 'href' => route('admin.settings.integrations')],
            ['key' => 'health', 'title' => 'System Health', 'href' => route('admin.settings.health')],
            ['key' => 'devtools', 'title' => 'DevTools', 'href' => route('admin.settings.devtools')],
        ];
    }
}
