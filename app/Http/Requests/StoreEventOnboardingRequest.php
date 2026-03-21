<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventOnboardingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'venue_address' => ['required', 'string', 'min:6', 'max:500'],
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
            'sub_events' => ['nullable', 'array', 'max:8'],
            'sub_events.*.key' => ['required', 'string', 'max:64', 'distinct'],
            'sub_events.*.label' => ['required', 'string', 'max:80'],
            'sub_events.*.date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:'.now()->addMonths((int) config('events.event_date_max_months', 18))->toDateString(),
            ],
            'sub_events.*.start_time' => ['required', 'date_format:H:i'],
            'timezone' => ['nullable', 'timezone:all'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $months = (int) config('events.event_date_max_months', 18);

        return [
            'wedding_partner_one_first_name.required_if' => 'Add the first partner name so we can suggest a polished wedding title.',
            'wedding_partner_two_first_name.required_if' => 'Add the second partner name so we can suggest a polished wedding title.',
            'wedding_family_name.required_if' => 'Add the family name so we can build wedding title suggestions.',
            'event_date.before_or_equal' => "Event dates can be at most {$months} months in the future.",
            'event_date.after_or_equal' => 'Event date cannot be in the past.',
            'event_dates.required' => 'Add at least one event date so we can prepare your album timeline.',
            'event_dates.*.date.required' => 'Each event date needs a calendar day.',
            'event_dates.*.date.before_or_equal' => "Event dates can be at most {$months} months in the future.",
            'event_dates.*.date.after_or_equal' => 'Event dates cannot be in the past.',
            'sub_events.*.date.required' => 'Each selected moment needs a date.',
            'sub_events.*.date.before_or_equal' => "Sub-event dates can be at most {$months} months in the future.",
            'sub_events.*.date.after_or_equal' => 'Sub-event dates cannot be in the past.',
            'sub_events.*.start_time.required' => 'Each selected moment needs a start time.',
        ];
    }
}
