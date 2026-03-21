<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;

test('login and register screens expose google auth when configured', function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'https://qrevents.test/auth/google/callback',
    ]);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Login')
            ->where('googleAuthEnabled', true)
            ->where('googleAuthUrl', route('auth.google.redirect'))
        );

    $this->get(route('register'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/Register')
            ->where('googleAuthEnabled', true)
            ->where('googleAuthUrl', route('auth.google.redirect'))
        );
});

test('google redirect sends guests to google consent screen', function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'https://qrevents.test/auth/google/callback',
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')
        ->once()
        ->with(['openid', 'profile', 'email'])
        ->andReturnSelf();
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));

    $factory = Mockery::mock(SocialiteFactory::class);
    $factory->shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $this->app->instance(SocialiteFactory::class, $factory);

    $this->get(route('auth.google.redirect'))
        ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('google redirect returns an inertia location response for inertia requests', function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'https://qrevents.test/auth/google/callback',
    ]);

    $this->withoutMiddleware(HandleInertiaRequests::class);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('scopes')
        ->once()
        ->with(['openid', 'profile', 'email'])
        ->andReturnSelf();
    $provider->shouldReceive('redirect')
        ->once()
        ->andReturn(redirect()->away('https://accounts.google.com/o/oauth2/auth'));

    $factory = Mockery::mock(SocialiteFactory::class);
    $factory->shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $this->app->instance(SocialiteFactory::class, $factory);

    $this->withHeaders([
        'X-Inertia' => 'true',
    ])->get(route('auth.google.redirect'))
        ->assertStatus(409)
        ->assertHeader('X-Inertia-Location', 'https://accounts.google.com/o/oauth2/auth');
});

test('google callback creates a new verified user and logs them in', function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'https://qrevents.test/auth/google/callback',
    ]);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->once()->andReturn('google-user-123');
    $socialUser->shouldReceive('getEmail')->once()->andReturn('new-google-user@example.com');
    $socialUser->shouldReceive('getName')->once()->andReturn('Google User');
    $socialUser->shouldReceive('getNickname')->zeroOrMoreTimes()->andReturn(null);
    $socialUser->shouldReceive('getAvatar')->once()->andReturn('https://example.com/google-avatar.jpg');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialUser);

    $factory = Mockery::mock(SocialiteFactory::class);
    $factory->shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $this->app->instance(SocialiteFactory::class, $factory);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'new-google-user@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);

    expect($user->google_id)->toBe('google-user-123')
        ->and($user->google_avatar)->toBe('https://example.com/google-avatar.jpg')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->account_type)->toBe(User::ACCOUNT_TYPE_USER);
});

test('google callback links an existing account by email', function () {
    config([
        'services.google.client_id' => 'google-client-id',
        'services.google.client_secret' => 'google-client-secret',
        'services.google.redirect' => 'https://qrevents.test/auth/google/callback',
    ]);

    $user = User::factory()->unverified()->create([
        'email' => 'existing-user@example.com',
        'google_id' => null,
        'google_avatar' => null,
    ]);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->once()->andReturn('google-user-456');
    $socialUser->shouldReceive('getEmail')->once()->andReturn('existing-user@example.com');
    $socialUser->shouldReceive('getName')->once()->andReturn('Existing User');
    $socialUser->shouldReceive('getNickname')->zeroOrMoreTimes()->andReturn(null);
    $socialUser->shouldReceive('getAvatar')->once()->andReturn('https://example.com/linked-google-avatar.jpg');

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($socialUser);

    $factory = Mockery::mock(SocialiteFactory::class);
    $factory->shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);

    $this->app->instance(SocialiteFactory::class, $factory);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();

    $this->assertAuthenticatedAs($user);

    expect($user->google_id)->toBe('google-user-456')
        ->and($user->google_avatar)->toBe('https://example.com/linked-google-avatar.jpg')
        ->and($user->email_verified_at)->not->toBeNull();
});
