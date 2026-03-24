<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventInvitationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template' => ['required', 'string', Rule::in(['classic', 'floral', 'midnight'])],
            'headline' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:1500'],
            'closing' => ['nullable', 'string', 'max:240'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'public_rsvp_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'headline' => $this->normalizeNullableString($this->input('headline')),
            'message' => $this->normalizeNullableString($this->input('message')),
            'closing' => $this->normalizeNullableString($this->input('closing')),
            'contact_phone' => $this->normalizeNullableString($this->input('contact_phone')),
            'public_rsvp_enabled' => filter_var($this->input('public_rsvp_enabled', false), FILTER_VALIDATE_BOOL),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsPayload(): array
    {
        return [
            'template' => $this->validated('template'),
            'headline' => $this->validated('headline'),
            'message' => $this->validated('message'),
            'closing' => $this->validated('closing'),
            'contact_phone' => $this->validated('contact_phone'),
            'public_rsvp_enabled' => (bool) $this->validated('public_rsvp_enabled', false),
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
