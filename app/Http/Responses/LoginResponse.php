<?php

namespace App\Http\Responses;

use App\Support\AuthOnboardingRedirector;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private AuthOnboardingRedirector $redirector) {}

    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended(
            $this->redirector->fallbackPathFor($request->user()),
        );
    }
}
