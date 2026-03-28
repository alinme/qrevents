<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessWalletCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->isBusinessAccount()
            && $this->user()->hasCompletedBusinessOnboarding();
    }

    public function rules(): array
    {
        return [
            'credits' => ['required', 'integer', Rule::in(array_map(
                static fn (array $pack): int => (int) ($pack['credits'] ?? 0),
                config('business.top_up_packs', []),
            ))],
            'currency' => ['required', 'string', Rule::in(config('business.supported_checkout_currencies', ['EUR', 'RON', 'GBP']))],
        ];
    }
}
