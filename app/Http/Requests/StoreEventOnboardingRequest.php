<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_slug' => [
                'required',
                'string',
                Rule::exists('plans', 'slug')->where(
                    fn ($query) => $query->where('is_active', true),
                ),
            ],
            'type' => ['required', 'string', Rule::in([
                'wedding',
                'party',
                'birthday',
                'engagement',
                'baptism',
                'other',
            ])],
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'wedding_partner_one_first_name' => ['nullable', 'string', 'max:60', 'required_if:type,wedding'],
            'wedding_partner_two_first_name' => ['nullable', 'string', 'max:60', 'required_if:type,wedding'],
            'wedding_family_name' => ['nullable', 'string', 'max:80', 'required_if:type,wedding'],
            'attendee_estimate' => ['required', 'integer', 'min:1', 'max:100000'],
            'event_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
                'before_or_equal:'.now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            ],
            'event_dates' => ['required', 'array', 'min:1', 'max:8'],
            'event_dates.*.label' => ['nullable', 'string', 'max:80'],
            'event_dates.*.date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:'.now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            ],
            'sub_events' => ['required', 'array', 'min:1', 'max:8'],
            'sub_events.*.key' => ['required', 'string', 'max:64', 'distinct'],
            'sub_events.*.label' => ['required', 'string', 'max:80'],
            'sub_events.*.date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:'.now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            ],
            'sub_events.*.start_time' => ['required', 'date_format:H:i'],
            'sub_events.*.address' => ['nullable', 'string', 'min:6', 'max:500'],
            'sub_events.*.no_address' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $subEvents = $this->input('sub_events', []);
                $type = (string) $this->input('type', '');

                if (! is_array($subEvents)) {
                    return;
                }

                $minimumRequiredSubEvents = match ($type) {
                    'baptism' => 2,
                    default => 1,
                };

                if (count($subEvents) < $minimumRequiredSubEvents) {
                    $validator->errors()->add(
                        'sub_events',
                        $minimumRequiredSubEvents === 2
                            ? 'Choose at least two relevant moments for this event type.'
                            : 'Choose at least one relevant moment for this event type.',
                    );
                }

                foreach ($subEvents as $index => $subEvent) {
                    if (! is_array($subEvent)) {
                        continue;
                    }

                    $address = is_string($subEvent['address'] ?? null) ? trim((string) $subEvent['address']) : '';
                    $noAddress = filter_var($subEvent['no_address'] ?? false, FILTER_VALIDATE_BOOL);

                    if (! $noAddress && $address === '') {
                        $validator->errors()->add(
                            "sub_events.{$index}.address",
                            'Add an address for this moment or mark it as having no address.',
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        $months = (int) config('events.event_date_max_months', 18);

        return [
            'plan_slug.required' => 'Choose a plan before creating the event.',
            'plan_slug.exists' => 'Choose an available plan.',
            'wedding_partner_one_first_name.required_if' => 'Add the first partner name so we can suggest a polished wedding title.',
            'wedding_partner_two_first_name.required_if' => 'Add the second partner name so we can suggest a polished wedding title.',
            'wedding_family_name.required_if' => 'Add the family name so we can build wedding title suggestions.',
            'event_date.before_or_equal' => "Event dates can be at most {$months} months in the future.",
            'event_date.after_or_equal' => 'Event date cannot be in the past.',
            'event_dates.required' => 'Add at least one event date so we can prepare your album timeline.',
            'event_dates.*.date.required' => 'Each event date needs a calendar day.',
            'event_dates.*.date.before_or_equal' => "Event dates can be at most {$months} months in the future.",
            'event_dates.*.date.after_or_equal' => 'Event dates cannot be in the past.',
            'sub_events.required' => 'Choose the moments that matter for this event.',
            'sub_events.*.date.required' => 'Each selected moment needs a date.',
            'sub_events.*.date.before_or_equal' => "Sub-event dates can be at most {$months} months in the future.",
            'sub_events.*.date.after_or_equal' => 'Sub-event dates cannot be in the past.',
            'sub_events.*.start_time.required' => 'Each selected moment needs a start time.',
            'sub_events.*.address.min' => 'Moment addresses should include enough detail for guests to find the place.',
        ];
    }
}
