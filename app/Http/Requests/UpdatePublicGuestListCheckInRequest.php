<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicGuestListCheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_attendance_status' => ['required', 'string', Rule::in(['unknown', 'present', 'absent'])],
            'event_table_id' => ['nullable', 'integer'],
            'confirmed_attendees_count' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    /**
     * @return array{actual_attendance_status: string, confirmed_attendees_count: int|null, actual_attendees_count: int|null, actual_attendance_recorded_at: \Illuminate\Support\Carbon|null, event_table_id: int|null}
     */
    public function payload(): array
    {
        $status = (string) $this->validated('actual_attendance_status');
        $confirmedAttendeesCount = $this->input('confirmed_attendees_count') === null || $this->input('confirmed_attendees_count') === ''
            ? null
            : (int) $this->input('confirmed_attendees_count');

        return [
            'actual_attendance_status' => $status,
            'confirmed_attendees_count' => $confirmedAttendeesCount,
            'actual_attendees_count' => match ($status) {
                'present' => $confirmedAttendeesCount,
                'absent' => 0,
                default => null,
            },
            'actual_attendance_recorded_at' => $status === 'unknown' ? null : now(),
            'event_table_id' => $this->input('event_table_id') === null || $this->input('event_table_id') === ''
                ? null
                : (int) $this->input('event_table_id'),
        ];
    }
}
