<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AuthOnboardingRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class SocialAuthController extends Controller
{
    private const SCREEN_LOGIN = 'login';

    private const SCREEN_REGISTER = 'register';

    private const SCREEN_REGISTER_BUSINESS = 'register-business';

    public function __construct(private AuthOnboardingRedirector $redirector) {}

    public function redirect(Request $request): RedirectResponse|SymfonyResponse
    {
        if (! $this->googleIsConfigured()) {
            return $this->redirectWithSocialAuthError($request, 'Google sign-in is not configured yet.');
        }

        if (Auth::check()) {
            return redirect()->to($this->redirector->fallbackPathFor($request->user()));
        }

        $this->rememberSocialAuthContext($request);

        $redirectResponse = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();

        if ($request->header('X-Inertia')) {
            return Inertia::location($redirectResponse);
        }

        return $redirectResponse;
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->googleIsConfigured()) {
            return $this->redirectWithSocialAuthError($request, 'Google sign-in is not configured yet.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            Log::warning('Google authentication failed.', [
                'message' => $exception->getMessage(),
            ]);

            return $this->redirectWithSocialAuthError(
                $request,
                'Google sign-in could not be completed. Please try again.',
            );
        }

        $email = Str::lower(trim((string) $googleUser->getEmail()));
        $googleId = trim((string) $googleUser->getId());
        $name = trim((string) ($googleUser->getName() ?: $googleUser->getNickname() ?: ''));
        $avatar = trim((string) ($googleUser->getAvatar() ?? ''));

        if ($email === '' || $googleId === '') {
            return $this->redirectWithSocialAuthError(
                $request,
                'Your Google account did not return the required profile details.',
            );
        }

        $accountType = $this->pullRequestedAccountType($request);

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
                'account_type' => $accountType,
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
        $request->session()->forget('social_auth_screen');

        return redirect()->intended($this->redirector->fallbackPathFor($user));
    }

    private function rememberSocialAuthContext(Request $request): void
    {
        $screen = $this->normalizeScreen($request->query('screen'));
        $accountType = $this->normalizeAccountType($request->query('account_type'));

        if ($screen === null) {
            $request->session()->forget('social_auth_screen');
        } else {
            $request->session()->put('social_auth_screen', $screen);
        }

        if ($accountType === null) {
            $request->session()->forget('social_auth_account_type');
        } else {
            $request->session()->put('social_auth_account_type', $accountType);
        }
    }

    private function redirectWithSocialAuthError(Request $request, string $message): RedirectResponse
    {
        $request->session()->forget('social_auth_account_type');

        return to_route($this->pullSocialAuthRedirectRoute($request))
            ->with('social_auth_error', $message);
    }

    private function pullRequestedAccountType(Request $request): string
    {
        $accountType = $request->session()->pull('social_auth_account_type');

        return $accountType === User::ACCOUNT_TYPE_BUSINESS
            ? User::ACCOUNT_TYPE_BUSINESS
            : User::ACCOUNT_TYPE_USER;
    }

    private function pullSocialAuthRedirectRoute(Request $request): string
    {
        return match ($request->session()->pull('social_auth_screen')) {
            self::SCREEN_REGISTER => 'register',
            self::SCREEN_REGISTER_BUSINESS => 'register.business',
            default => 'login',
        };
    }

    private function normalizeScreen(mixed $screen): ?string
    {
        if (! is_string($screen)) {
            return null;
        }

        return match ($screen) {
            self::SCREEN_LOGIN,
            self::SCREEN_REGISTER,
            self::SCREEN_REGISTER_BUSINESS => $screen,
            default => null,
        };
    }

    private function normalizeAccountType(mixed $accountType): ?string
    {
        if (! is_string($accountType)) {
            return null;
        }

        return $accountType === User::ACCOUNT_TYPE_BUSINESS
            ? User::ACCOUNT_TYPE_BUSINESS
            : null;
    }

    private function googleIsConfigured(): bool
    {
        return trim((string) config('services.google.client_id')) !== ''
            && trim((string) config('services.google.client_secret')) !== ''
            && trim((string) config('services.google.redirect')) !== '';
    }
}
