<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessDashboardRequest extends FormRequest
{
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
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'in:all,attention,unpaid,overdue,live,export_ready'],
            'selection_scope' => ['nullable', 'string', 'in:all_filtered'],
            'page' => ['nullable', 'integer', 'min:1'],
            'event_ids' => ['nullable', 'array'],
            'event_ids.*' => ['integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.max' => 'Search must stay under 120 characters.',
            'status.in' => 'Choose a valid business dashboard filter.',
            'selection_scope.in' => 'Choose a valid business dashboard selection scope.',
            'page.integer' => 'Page must be a valid number.',
            'page.min' => 'Page must be at least 1.',
            'event_ids.array' => 'Selected events must be sent as a list.',
            'event_ids.*.integer' => 'Selected event ids must be valid numbers.',
            'event_ids.*.min' => 'Selected event ids must be valid.',
        ];
    }
}
