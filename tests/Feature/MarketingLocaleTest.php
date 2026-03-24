<?php

use Inertia\Testing\AssertableInertia as Assert;

it('uses the site locale cookie on the marketing home page', function () {
    $this->withUnencryptedCookies(['site_locale' => 'ro'])
        ->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('pwaLaunch', false)
            ->where('locale.current', 'ro')
            ->where('translations.marketing.nav.pricing', 'Preturi')
            ->where('translations.marketing.home.hero.title', 'Colecteaza usor fotografii de la fiecare invitat la evenimentul tau')
            ->where('translations.marketing.pricing.hero.title', 'Alege planul care se potriveste cel mai bine evenimentului tau')
            ->where('translations.marketing.weddings.hero.title', 'Un album foto de nunta pe care invitatii chiar vor sa-l foloseasca in ziua evenimentului')
            ->where('translations.marketing.businesses.hero.title', 'Partajare foto de eveniment pentru echipe care fac asta din nou si din nou')
            ->where('translations.public.album.actions.open_viewer', 'Deschide viewer-ul')
        );
});

it('renders the pwa launch entry on the welcome page component', function () {
    $this->get(route('launch'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('pwaLaunch', true)
            ->where('canRegister', true)
        );
});
