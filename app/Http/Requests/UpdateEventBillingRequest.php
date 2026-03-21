<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventBillingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_id' => [
                'required',
                'integer',
                Rule::exists('plans', 'id')->where(
                    fn ($query) => $query->where('is_active', true),
                ),
            ],
            'is_paid' => ['required', 'boolean'],
            'payment_due_at' => ['nullable', 'date_format:Y-m-d'],
            'paid_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'billing_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan_id.required' => 'Select an active billing plan.',
            'plan_id.exists' => 'Select a valid active billing plan.',
            'payment_due_at.date_format' => 'Payment due date must use the YYYY-MM-DD format.',
            'paid_at.date_format' => 'Paid at must use the local date and time format.',
            'billing_note.max' => 'Billing note must stay under 1000 characters.',
        ];
    }
}
