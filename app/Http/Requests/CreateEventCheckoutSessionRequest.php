<?php

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

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
        return [];
    }
}
