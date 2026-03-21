<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RunEventCleanupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:clear_export,purge_media'],
            'confirmation_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.in' => 'Choose a valid cleanup action.',
            'confirmation_name.required' => 'Type the event name to confirm cleanup.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Event|null $event */
                $event = $this->route('event');

                if (! $event instanceof Event) {
                    return;
                }

                $confirmationName = trim((string) $this->string('confirmation_name'));
                if ($confirmationName !== $event->name) {
                    $validator->errors()->add(
                        'confirmation_name',
                        'Type the exact event name to confirm this cleanup action.',
                    );
                }
            },
        ];
    }
}
