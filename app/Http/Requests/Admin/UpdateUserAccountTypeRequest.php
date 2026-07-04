<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccountTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'account_type' => ['required', 'string', Rule::in(User::assignableAccountTypes())],
        ];
    }
}
