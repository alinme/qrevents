<?php

use App\Models\AdminAuditLog;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SettingsRepository;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

function settingsAdmin(): User
{
    return User::factory()->create([
        'email' => 'root@example.com',
        'account_type' => User::ACCOUNT_TYPE_SUPER_ADMIN,
    ]);
}

test('regular users cannot access site settings', function (string $route) {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route($route))->assertForbidden();
})->with([
    'admin.settings.general',
    'admin.settings.email',
    'admin.settings.seo',
    'admin.settings.integrations',
    'admin.settings.payments',
    'admin.settings.health',
    'admin.settings.devtools',
]);

test('super admins can view general settings with the tab bar', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->get(route('admin.settings.general'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/settings/General')
            ->where('activeTab', 'general')
            ->has('settingsTabs', 7)
        );
});

test('saving general settings persists values and is audited', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->put(route('admin.settings.general.update'), [
            'site_name' => 'EventSmart Pro',
            'company_name' => 'WV Events',
            'public_email' => 'hello@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(app(SettingsRepository::class)->get('general.site_name'))->toBe('EventSmart Pro');
    expect(AdminAuditLog::query()->where('action', 'settings.general.updated')->count())->toBe(1);
});

test('smtp password is encrypted at rest and never exposed', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.email.update'), [
        'host' => 'smtp.example.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'mailer',
        'password' => 'super-secret-pw',
        'from_address' => 'from@example.com',
        'from_name' => 'EventSmart',
    ])->assertRedirect();

    // stored encrypted (raw value is not the plaintext)
    $row = SiteSetting::query()->where('key', 'mail.password')->firstOrFail();
    expect($row->is_encrypted)->toBeTrue();
    expect($row->value)->not->toContain('super-secret-pw');

    // repository can still read it back
    expect(app(SettingsRepository::class)->get('mail.password'))->toBe('super-secret-pw');

    // the page never sends the password to the browser
    $this->actingAs($admin)
        ->get(route('admin.settings.email'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('passwordSet', true)
            ->missing('values.password')
        );
});

test('a blank password keeps the existing one', function () {
    $admin = settingsAdmin();
    $repo = app(SettingsRepository::class);
    $repo->set('mail.password', 'original', 'email', true);

    $this->actingAs($admin)->put(route('admin.settings.email.update'), [
        'host' => 'smtp.example.com',
        'port' => 587,
        'encryption' => 'tls',
        'password' => '',
    ])->assertRedirect();

    expect(app(SettingsRepository::class)->get('mail.password'))->toBe('original');
});

test('a test email can be sent to the admin', function () {
    // The array transport captures the message without opening a connection.
    config(['mail.default' => 'array', 'mail.from.address' => 'test@example.com', 'mail.from.name' => 'Test']);
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->post(route('admin.settings.email.test'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(app('mailer')->getSymfonyTransport()->messages())->toHaveCount(1);
    expect(AdminAuditLog::query()->where('action', 'settings.email.tested')->count())->toBe(1);
});

test('seo settings save per language', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.seo.update'), [
        'values' => [
            'en' => ['title' => 'EN title', 'description' => 'EN desc'],
            'ro' => ['title' => 'RO title', 'description' => 'RO desc'],
            'el' => ['title' => 'EL title', 'description' => 'EL desc'],
        ],
        'social' => ['facebook' => 'https://facebook.com/eventsmart'],
    ])->assertRedirect();

    $repo = app(SettingsRepository::class);
    expect($repo->get('seo.title.ro'))->toBe('RO title');
    expect($repo->get('seo.description.el'))->toBe('EL desc');
    expect($repo->get('social.facebook'))->toBe('https://facebook.com/eventsmart');
});

test('integrations save and encrypt the storage secret', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.integrations.update'), [
        'storage' => [
            'access_key' => 'AKIAXXXX',
            'secret' => 'the-secret',
            'region' => 'fr-par',
            'bucket' => 'eventsmart',
            'endpoint' => 'https://s3.fr-par.scw.cloud',
        ],
    ])->assertRedirect();

    $repo = app(SettingsRepository::class);
    expect($repo->get('storage.bucket'))->toBe('eventsmart');
    expect($repo->get('storage.secret'))->toBe('the-secret');
    expect(SiteSetting::query()->where('key', 'storage.secret')->first()->is_encrypted)->toBeTrue();
});

test('stripe keys save with secrets encrypted and the publishable key in the clear', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.payments.update'), [
        'key' => 'pk_test_123',
        'secret' => 'sk_test_supersecret',
        'webhook_secret' => 'whsec_topsecret',
    ])->assertRedirect();

    $repo = app(SettingsRepository::class);
    expect($repo->get('stripe.key'))->toBe('pk_test_123');
    expect($repo->get('stripe.secret'))->toBe('sk_test_supersecret');
    expect($repo->get('stripe.webhook_secret'))->toBe('whsec_topsecret');

    // secrets encrypted at rest; publishable key stored plain
    expect(SiteSetting::query()->where('key', 'stripe.secret')->first()->is_encrypted)->toBeTrue();
    expect(SiteSetting::query()->where('key', 'stripe.webhook_secret')->first()->is_encrypted)->toBeTrue();
    expect(SiteSetting::query()->where('key', 'stripe.key')->first()->is_encrypted)->toBeFalse();

    // the page masks the secret (only booleans) and detects test mode
    $this->actingAs($admin)
        ->get(route('admin.settings.payments'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('secretSet', true)
            ->where('webhookSecretSet', true)
            ->where('mode', 'test')
            ->where('publishableKey', 'pk_test_123')
            ->missing('secret')
        );
});

test('a blank stripe secret keeps the existing one', function () {
    $admin = settingsAdmin();
    app(SettingsRepository::class)->set('stripe.secret', 'sk_live_original', 'payments', true);

    $this->actingAs($admin)->put(route('admin.settings.payments.update'), [
        'key' => 'pk_live_abc',
        'secret' => '',
        'webhook_secret' => '',
    ])->assertRedirect();

    expect(app(SettingsRepository::class)->get('stripe.secret'))->toBe('sk_live_original');
});

test('stripe settings from the store override runtime config', function () {
    app(SettingsRepository::class)->setMany('payments', [
        'stripe.key' => 'pk_live_runtime',
        'stripe.secret' => 'sk_live_runtime',
        'stripe.webhook_secret' => 'whsec_runtime',
    ], encryptedKeys: ['stripe.secret', 'stripe.webhook_secret']);

    (new App\Providers\SettingsServiceProvider(app()))->boot();

    expect(config('services.stripe.secret'))->toBe('sk_live_runtime');
    expect(config('services.stripe.key'))->toBe('pk_live_runtime');
    expect(config('services.stripe.webhook_secret'))->toBe('whsec_runtime');
});

test('health data returns a json snapshot', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->getJson(route('admin.settings.health.data'))
        ->assertOk()
        ->assertJsonStructure(['queue' => ['pending', 'failed'], 'checks', 'system' => ['phpVersion']]);
});

test('devtools rejects any operation outside the allowlist', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->post(route('admin.settings.devtools.run'), ['operation' => 'rm -rf /'])
        ->assertSessionHasErrors('operation');
});

test('devtools runs an allowlisted operation and audits it', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)
        ->post(route('admin.settings.devtools.run'), ['operation' => 'caches_clear'])
        ->assertRedirect()
        ->assertSessionHas('devtoolsResult');

    expect(AdminAuditLog::query()->where('action', 'settings.devtools.run')->count())->toBe(1);
});

test('regular users cannot run devtools', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.settings.devtools.run'), ['operation' => 'caches_clear'])
        ->assertForbidden();
});
