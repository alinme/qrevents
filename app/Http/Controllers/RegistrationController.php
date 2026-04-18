<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class RegistrationController extends Controller
{
    public function business(Request $request): Response
    {
        abort_unless(Features::enabled(Features::registration()), 404);

        return Inertia::render('auth/Register', [
            'registrationMode' => 'business',
            'businessRegisterUrl' => route('register.business'),
            ...$this->socialAuthViewProps($request),
        ]);
    }

    /**
     * @return array{googleAuthEnabled: bool, googleAuthUrl: string|null, socialAuthError: string|null}
     */
    private function socialAuthViewProps(Request $request): array
    {
        $googleAuthEnabled = trim((string) config('services.google.client_id')) !== ''
            && trim((string) config('services.google.client_secret')) !== ''
            && trim((string) config('services.google.redirect')) !== '';

        return [
            'googleAuthEnabled' => $googleAuthEnabled,
            'googleAuthUrl' => $googleAuthEnabled
                ? route('auth.google.redirect', [
                    'screen' => 'register-business',
                    'account_type' => 'business',
                ])
                : null,
            'socialAuthError' => $request->session()->get('social_auth_error'),
        ];
    }
}
