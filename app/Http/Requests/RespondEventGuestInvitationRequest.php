<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondEventGuestInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:2', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'invited_attendees_count' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'attendance_status' => ['required', 'string', Rule::in(['accepted', 'declined'])],
            'confirmed_attendees_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'guest_names' => ['nullable', 'string', 'max:1500'],
            'meal_preference' => ['nullable', 'string', Rule::in(['standard', 'vegetarian', 'vegan', 'halal', 'other'])],
            'response_notes' => ['nullable', 'string', 'max:1500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Add the family or guest name before sending the RSVP.',
            'attendance_status.required' => 'Choose if this invitation is accepted or declined.',
            'confirmed_attendees_count.required' => 'Add how many people are joining this event.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->normalizeNullableString($this->input('name')),
            'phone' => $this->normalizeNullableString($this->input('phone')),
            'guest_names' => $this->normalizeNullableString($this->input('guest_names')),
            'response_notes' => $this->normalizeNullableString($this->input('response_notes')),
            'meal_preference' => $this->normalizeNullableString($this->input('meal_preference')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function responsePayload(): array
    {
        $validated = $this->validated();
        $attendanceStatus = (string) $validated['attendance_status'];
        $invitedAttendeesCount = (int) ($validated['invited_attendees_count'] ?? 1);

        $confirmedAttendeesCount = $attendanceStatus === 'accepted'
            ? (int) ($validated['confirmed_attendees_count'] ?? $invitedAttendeesCount)
            : 0;

        return [
            'attendance_status' => $attendanceStatus,
            'confirmed_attendees_count' => $confirmedAttendeesCount,
            'guest_names' => $validated['guest_names'] ?? null,
            'meal_preference' => $validated['meal_preference'] ?? null,
            'response_notes' => $validated['response_notes'] ?? null,
            'response_ip_address' => $this->ip(),
            'response_user_agent' => $this->userAgent(),
            'responded_at' => now(),
            'invitation_status' => 'responded',
        ];
    }

    /**
     * @return array{name: string, phone: ?string, invited_attendees_count: int}
     */
    public function publicPartyPayload(): array
    {
        return [
            'name' => trim((string) $this->input('name')),
            'phone' => $this->normalizeNullableString($this->input('phone')),
            'invited_attendees_count' => max(1, (int) ($this->input('invited_attendees_count') ?: 1)),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $isPublicRoute = $this->routeIs('events.guests.public-invitation.respond');
            $attendanceStatus = (string) $this->input('attendance_status');

            if ($isPublicRoute && trim((string) $this->input('name')) === '') {
                $validator->errors()->add('name', 'Add the family or guest name before sending the RSVP.');
            }

            if (
                $attendanceStatus === 'accepted'
                && $this->input('confirmed_attendees_count') === null
                && (int) ($this->input('invited_attendees_count') ?: 0) <= 0
            ) {
                $validator->errors()->add('confirmed_attendees_count', 'Add how many people are joining this event.');
            }
        });
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
