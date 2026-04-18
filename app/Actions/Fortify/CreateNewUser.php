<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'account_type' => ['nullable', 'string', Rule::in([
                User::ACCOUNT_TYPE_USER,
                User::ACCOUNT_TYPE_BUSINESS,
            ])],
            'password' => $this->passwordRules(),
        ])->validate();

        $accountType = ($input['account_type'] ?? null) === User::ACCOUNT_TYPE_BUSINESS
            ? User::ACCOUNT_TYPE_BUSINESS
            : User::ACCOUNT_TYPE_USER;

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'account_type' => $accountType,
        ]);
    }
}
