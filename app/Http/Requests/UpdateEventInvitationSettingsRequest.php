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
            'template' => ['required', 'string', Rule::in(['classic', 'floral', 'midnight', 'canva_cream', 'canva_brown', 'canva_watercolor'])],
            'headline' => ['nullable', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:1500'],
            'closing' => ['nullable', 'string', 'max:240'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'public_rsvp_enabled' => ['nullable', 'boolean'],
            'content' => ['nullable', 'array'],
            'content.partner_one_name' => ['nullable', 'string', 'max:80'],
            'content.partner_two_name' => ['nullable', 'string', 'max:80'],
            'content.family_name' => ['nullable', 'string', 'max:80'],
            'content.show_family_name' => ['nullable', 'boolean'],
            'content.bride_parents' => ['nullable', 'string', 'max:160'],
            'content.groom_parents' => ['nullable', 'string', 'max:160'],
            'content.godparents' => ['nullable', 'string', 'max:160'],
            'content.date_text' => ['nullable', 'string', 'max:120'],
            'content.venue_text' => ['nullable', 'string', 'max:180'],
            'visibility' => ['nullable', 'array'],
            'visibility.couple' => ['nullable', 'boolean'],
            'visibility.parents' => ['nullable', 'boolean'],
            'visibility.godparents' => ['nullable', 'boolean'],
            'visibility.date' => ['nullable', 'boolean'],
            'visibility.venue' => ['nullable', 'boolean'],
            'visibility.contact_phone' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalizedContent = $this->normalizeContent($this->input('content'));
        $contactPhone = $this->normalizeNullableString($this->input('contact_phone'));

        $this->merge([
            'headline' => $this->normalizeNullableString($this->input('headline')),
            'message' => $this->normalizeNullableString($this->input('message')),
            'closing' => $this->normalizeNullableString($this->input('closing')),
            'contact_phone' => $contactPhone,
            'public_rsvp_enabled' => filter_var($this->input('public_rsvp_enabled', false), FILTER_VALIDATE_BOOL),
            'content' => $normalizedContent,
            'visibility' => $this->normalizeVisibility($this->input('visibility'), $normalizedContent, $contactPhone),
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
            'content' => $this->validated('content', []),
            'visibility' => $this->validated('visibility', []),
        ];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeContent(mixed $value): array
    {
        $content = is_array($value) ? $value : [];

        return [
            'partner_one_name' => $this->normalizeNullableString($content['partner_one_name'] ?? null),
            'partner_two_name' => $this->normalizeNullableString($content['partner_two_name'] ?? null),
            'family_name' => $this->normalizeNullableString($content['family_name'] ?? null),
            'show_family_name' => filter_var($content['show_family_name'] ?? false, FILTER_VALIDATE_BOOL),
            'bride_parents' => $this->normalizeNullableString($content['bride_parents'] ?? null),
            'groom_parents' => $this->normalizeNullableString($content['groom_parents'] ?? null),
            'godparents' => $this->normalizeNullableString($content['godparents'] ?? null),
            'date_text' => $this->normalizeNullableString($content['date_text'] ?? null),
            'venue_text' => $this->normalizeNullableString($content['venue_text'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return array<string, bool>
     */
    private function normalizeVisibility(mixed $value, array $content, ?string $contactPhone): array
    {
        $visibility = is_array($value) ? $value : [];

        return [
            'couple' => filter_var($visibility['couple'] ?? true, FILTER_VALIDATE_BOOL),
            'parents' => filter_var(
                $visibility['parents'] ?? ($content['bride_parents'] !== null || $content['groom_parents'] !== null),
                FILTER_VALIDATE_BOOL,
            ),
            'godparents' => filter_var(
                $visibility['godparents'] ?? ($content['godparents'] !== null),
                FILTER_VALIDATE_BOOL,
            ),
            'date' => filter_var(
                $visibility['date'] ?? ($content['date_text'] !== null),
                FILTER_VALIDATE_BOOL,
            ),
            'venue' => filter_var(
                $visibility['venue'] ?? ($content['venue_text'] !== null),
                FILTER_VALIDATE_BOOL,
            ),
            'contact_phone' => filter_var(
                $visibility['contact_phone'] ?? ($contactPhone !== null),
                FILTER_VALIDATE_BOOL,
            ),
        ];
    }
}
