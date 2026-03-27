<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpsertEventTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'seats_count' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Add a table name before saving.',
            'seats_count.required' => 'Add how many seats the table has.',
        ];
    }

    /**
     * @return array{name: string, seats_count: int}
     */
    public function payload(): array
    {
        return [
            'name' => trim((string) $this->validated('name')),
            'seats_count' => (int) $this->validated('seats_count'),
        ];
    }
}
