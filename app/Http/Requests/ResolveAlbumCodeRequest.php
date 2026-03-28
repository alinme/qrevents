<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ResolveAlbumCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rawCode = trim((string) $this->input('code', ''));

        if (preg_match('~/a/([A-Za-z0-9]+)~', $rawCode, $matches) === 1) {
            $rawCode = $matches[1];
        }

        $normalizedCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $rawCode) ?? '');

        $this->merge([
            'code' => $normalizedCode,
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'alpha_num', 'size:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Enter the album code from the QR card or short link.',
            'code.alpha_num' => 'Use only the letters and numbers from the album code.',
            'code.size' => 'Album codes use 32 letters and numbers. Keep going until every block is filled.',
        ];
    }

    public function normalizedCode(): string
    {
        return (string) $this->validated('code');
    }
}
