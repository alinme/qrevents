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
            ->has('settingsTabs', 6)
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
    ])->assertRedirect();

    $repo = app(SettingsRepository::class);
    expect($repo->get('seo.title.ro'))->toBe('RO title');
    expect($repo->get('seo.description.el'))->toBe('EL desc');
});

test('integrations save and encrypt the storage secret', function () {
    $admin = settingsAdmin();

    $this->actingAs($admin)->put(route('admin.settings.integrations.update'), [
        'social' => ['facebook' => 'https://facebook.com/eventsmart'],
        'storage' => [
            'access_key' => 'AKIAXXXX',
            'secret' => 'the-secret',
            'region' => 'nl-ams',
            'bucket' => 'eventsaas',
            'endpoint' => 'https://s3.nl-ams.scw.cloud',
        ],
    ])->assertRedirect();

    $repo = app(SettingsRepository::class);
    expect($repo->get('social.facebook'))->toBe('https://facebook.com/eventsmart');
    expect($repo->get('storage.bucket'))->toBe('eventsaas');
    expect($repo->get('storage.secret'))->toBe('the-secret');
    expect(SiteSetting::query()->where('key', 'storage.secret')->first()->is_encrypted)->toBeTrue();
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
