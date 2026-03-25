<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateEventGuestInvitationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_party_ids' => ['required', 'array', 'min:1'],
            'guest_party_ids.*' => ['required', 'integer', 'distinct'],
            'action' => ['required', 'string', Rule::in(['mark_delivered_in_person', 'mark_sent_online', 'mark_reminded_online'])],
            'delivery_channel' => [
                'nullable',
                'string',
                Rule::in(['in_person', 'phone', 'whatsapp', 'facebook', 'public_link', 'other']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_party_ids.required' => 'Select at least one guest party first.',
            'guest_party_ids.min' => 'Select at least one guest party first.',
            'action.required' => 'Choose what to do with the selected invitations.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $action = (string) $this->input('action');
        $deliveryChannel = $this->normalizeNullableString($this->input('delivery_channel'));

        $this->merge([
            'action' => $action,
            'delivery_channel' => match ($action) {
                'mark_delivered_in_person' => 'in_person',
                'mark_sent_online', 'mark_reminded_online' => $deliveryChannel ?? 'other',
                default => $deliveryChannel,
            },
        ]);
    }

    /**
     * @return list<int>
     */
    public function guestPartyIds(): array
    {
        return collect($this->validated('guest_party_ids', []))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function actionName(): string
    {
        return (string) $this->validated('action');
    }

    public function deliveryChannel(): ?string
    {
        return $this->validated('delivery_channel');
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
