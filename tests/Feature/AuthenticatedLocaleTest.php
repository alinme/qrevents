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

it('shares localized strings on event media, invite studio, settings, and print pages', function () {
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
            ->where('translations.media.attendees.title', 'Participanti')
            ->where('eventNavigation.1.title', 'Media')
        );

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.invite-studio', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/InviteStudio')
            ->where('locale.current', 'ro')
            ->where('translations.guests.invitation.studio_title', 'Invitation Studio')
            ->where('translations.guests.invitation.simple_description', 'Alege fundalul, editeaza textul si deschide sau tipareste invitatia live dintr-un singur loc linistit.')
            ->where('translations.app.nav.invite_studio', 'Studio invitatii')
            ->where('eventNavigation.3.title', 'Studio invitatii')
        );

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.settings', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/Settings')
            ->where('locale.current', 'ro')
            ->where('translations.event_settings.page.title', 'Setari')
            ->where('translations.event_settings.general.name_title', 'Numele evenimentului')
            ->where('translations.event_settings.billing.owner_upgrade_title', 'Upgradeaza acest eveniment')
            ->where('eventNavigation.4.title', 'Setari')
        );

    $this->actingAs($owner)
        ->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('events.print-pack', $event))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('events/PrintPack')
            ->where('locale.current', 'ro')
            ->where('translations.event_home.print_pack.open_studio', 'Deschide Studio QR')
            ->where('translations.event_home.print_pack.background_title', 'Stil fundal')
            ->where('eventNavigation.5.title', 'Studio QR')
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
