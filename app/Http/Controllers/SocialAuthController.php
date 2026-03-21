<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirect(): RedirectResponse|SymfonyRedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return to_route('login')->with('social_auth_error', 'Google sign-in is not configured yet.');
        }

        if (Auth::check()) {
            return to_route('dashboard');
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return to_route('login')->with('social_auth_error', 'Google sign-in is not configured yet.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::warning('Google authentication failed.', [
                'message' => $exception->getMessage(),
            ]);

            return to_route('login')->with('social_auth_error', 'Google sign-in could not be completed. Please try again.');
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: ''));
        $avatar = trim((string) ($googleUser->getAvatar() ?? ''));

        if ($email === '' || $googleId === '') {
            return to_route('login')->with('social_auth_error', 'Your Google account did not return the required profile details.');
        }

        $user = User::query()
            ->where('google_id', $googleId)
            ->first();

        if (! $user instanceof User) {
            $user = User::query()
                ->where('email', $email)
                ->first();
        }

        if (! $user instanceof User) {
            $user = User::create([
                'name' => $name !== '' ? $name : Str::headline(Str::before($email, '@')),
                'email' => $email,
                'password' => Str::password(32),
                'account_type' => User::ACCOUNT_TYPE_USER,
                'google_id' => $googleId,
                'google_avatar' => $avatar !== '' ? $avatar : null,
            ]);
        } else {
            $updates = [];

            if ($user->google_id !== $googleId) {
                $updates['google_id'] = $googleId;
            }

            if ($avatar !== '' && $user->google_avatar !== $avatar) {
                $updates['google_avatar'] = $avatar;
            }

            if ($user->name === '' && $name !== '') {
                $updates['name'] = $name;
            }

            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }
        }

        if ($user->email_verified_at === null) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function googleIsConfigured(): bool
    {
        return trim((string) config('services.google.client_id')) !== ''
            && trim((string) config('services.google.client_secret')) !== ''
            && trim((string) config('services.google.redirect')) !== '';
    }
}
