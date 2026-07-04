<?php

namespace App\Http\Requests;

use Closure;
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
        $min = max(1, (int) config('business.credit_min_topup_eur', 100));
        $step = max(1, (int) config('business.credit_topup_step_eur', 50));

        return [
            // Euro amount the business prepays; the volume tier sets the rate.
            'amount' => [
                'required', 'integer', "min:{$min}", 'max:50000',
                function (string $attribute, mixed $value, Closure $fail) use ($min, $step): void {
                    if (((int) $value - $min) % $step !== 0) {
                        $fail("The amount must be in €{$step} increments.");
                    }
                },
            ],
            'currency' => ['required', 'string', Rule::in(config('business.supported_checkout_currencies', ['EUR', 'RON', 'GBP']))],
        ];
    }
}
