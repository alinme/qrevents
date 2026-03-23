<?php

namespace App\Http\Responses;

use App\Support\AuthOnboardingRedirector;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(private AuthOnboardingRedirector $redirector) {}

    public function toResponse($request): RedirectResponse
    {
        return redirect()->intended(
            $this->redirector->fallbackPathFor($request->user()),
        );
    }
}
