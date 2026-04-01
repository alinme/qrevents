<?php

use App\Models\Event;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shares localized strings on the login page', function () {
    $this->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->where('locale.current', 'ro')
            ->where('translations.auth.login.title', 'Conecteaza-te in contul tau')
            ->where('translations.auth.login.remember_me', 'Tine-ma minte')
            ->where('translations.auth.register.title', 'Creeaza un cont')
        );
});

it('shares localized strings on event media and settings pages', function () {
    $owner = User::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.media', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Media')
            ->where('locale.current', 'ro')
            ->where('translations.media.page.kicker', 'Workspace eveniment')
            ->where('translations.media.bulk.delete', 'Sterge selectate')
            ->where('eventNavigation.1.title', 'Media')
        );

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('locale.current', 'ro')
            ->where('translations.event_settings.page.title', 'Setari')
            ->where('translations.event_settings.billing.owner_upgrade_title', 'Upgradeaza acest eveniment')
            ->where('eventNavigation.3.title', 'Setari')
        );
});

it('shares localized strings on account settings pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->where('locale.current', 'ro')
            ->where('translations.account_settings.layout.title', 'Setari')
            ->where('translations.account_settings.profile.title', 'Informatii profil')
            ->where('translations.account_settings.delete_account.trigger', 'Sterge contul')
            ->where('accountNavigation.0.title', 'Evenimente')
        );
});
