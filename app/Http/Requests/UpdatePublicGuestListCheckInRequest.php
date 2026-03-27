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
        ];
    }

    /**
     * @return array{actual_attendance_status: string, actual_attendees_count: int|null, actual_attendance_recorded_at: \Illuminate\Support\Carbon|null}
     */
    public function payload(): array
    {
        $status = (string) $this->validated('actual_attendance_status');

        return [
            'actual_attendance_status' => $status,
            'actual_attendees_count' => match ($status) {
                'present' => null,
                'absent' => 0,
                default => null,
            },
            'actual_attendance_recorded_at' => $status === 'unknown' ? null : now(),
        ];
    }
}
