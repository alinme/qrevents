<?php

use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyFeature(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('business registration screen can be rendered', function () {
    $response = $this->get(route('register.business'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('onboarding.create', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect($user->account_type)->toBe(User::ACCOUNT_TYPE_USER);
});

test('business users can register from the dedicated business screen', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Studio Owner',
        'email' => 'studio@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'account_type' => User::ACCOUNT_TYPE_BUSINESS,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard.business.onboarding', absolute: false));

    $user = User::query()->where('email', 'studio@example.com')->firstOrFail();

    expect($user->account_type)->toBe(User::ACCOUNT_TYPE_BUSINESS)
        ->and($user->business_onboarded_at)->toBeNull();
});
