<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertEventGuestPartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'invited_attendees_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'confirmed_attendees_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'actual_attendees_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'attendance_status' => ['required', 'string', Rule::in(['pending', 'accepted', 'declined'])],
            'actual_attendance_status' => ['required', 'string', Rule::in(['unknown', 'present', 'absent'])],
            'notes' => ['nullable', 'string', 'max:1500'],
            'invitation_status' => ['required', 'string', Rule::in(['draft', 'delivered_in_person', 'sent', 'opened', 'responded'])],
            'invitation_delivery_channel' => ['nullable', 'string', Rule::in(['in_person', 'phone', 'whatsapp', 'facebook', 'public_link', 'other'])],
            'gift_type' => ['nullable', 'string', Rule::in(['money', 'gift'])],
            'gift_currency' => ['nullable', 'string', Rule::in(['EUR', 'GBP', 'RON'])],
            'gift_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Add a family or guest name before saving.',
            'invited_attendees_count.required' => 'Add how many people are invited in this party.',
            'gift_currency.in' => 'Gift currency must be EUR, GBP, or RON.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $giftType = $this->input('gift_type');

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $this->normalizeNullableString($this->input('phone')),
            'notes' => $this->normalizeNullableString($this->input('notes')),
            'gift_type' => $this->normalizeNullableString($giftType),
            'gift_currency' => $giftType === 'money'
                ? $this->normalizeNullableString($this->input('gift_currency'))
                : null,
            'gift_amount' => $giftType === 'money'
                ? $this->normalizeNullableString($this->input('gift_amount'))
                : null,
            'invitation_delivery_channel' => $this->normalizeNullableString($this->input('invitation_delivery_channel')),
            'actual_attendance_status' => $this->normalizeNullableString($this->input('actual_attendance_status')) ?? 'unknown',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $validated = $this->validated();

        if (($validated['attendance_status'] ?? null) === 'accepted' && ! isset($validated['confirmed_attendees_count'])) {
            $validated['confirmed_attendees_count'] = $validated['invited_attendees_count'] ?? 1;
        }

        if (($validated['attendance_status'] ?? null) !== 'accepted') {
            $validated['confirmed_attendees_count'] = null;
        }

        $actualAttendanceStatus = $validated['actual_attendance_status'] ?? 'unknown';
        if ($actualAttendanceStatus === 'present' && ! isset($validated['actual_attendees_count'])) {
            $validated['actual_attendees_count'] = $validated['confirmed_attendees_count']
                ?? $validated['invited_attendees_count']
                ?? 1;
        }

        if ($actualAttendanceStatus === 'absent') {
            $validated['actual_attendees_count'] = 0;
        }

        if ($actualAttendanceStatus === 'unknown') {
            $validated['actual_attendees_count'] = null;
        }

        $validated['actual_attendance_recorded_at'] = $actualAttendanceStatus === 'unknown'
            ? null
            : now();

        if (($validated['gift_type'] ?? null) !== 'money') {
            $validated['gift_currency'] = null;
            $validated['gift_amount'] = null;
        }

        $validated['invitation_delivered_at'] = ($validated['invitation_status'] ?? null) === 'delivered_in_person'
            ? now()
            : null;

        return $validated;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
