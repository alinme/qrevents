<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventCheckoutSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $this->user() !== null
            && $event instanceof Event
            && (
                $this->user()->id === $event->user_id
                || $this->user()->canAccessAdmin()
            );
    }

    public function rules(): array
    {
        return [
            'plan_id' => [
                'nullable',
                'integer',
                Rule::exists('plans', 'id')->where(
                    fn ($query) => $query->where('is_active', true),
                ),
            ],
        ];
    }
}
