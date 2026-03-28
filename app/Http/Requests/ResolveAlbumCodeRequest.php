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
        $target = (string) $this->input('target', 'album');

        if (preg_match('~/([aw])/([A-Za-z0-9]+)~i', $rawCode, $matches) === 1) {
            $target = Str::lower($matches[1]) === 'w' ? 'wall' : 'album';
            $rawCode = $matches[2];
        } elseif (preg_match('~/([aw][A-Za-z0-9]{4})$~i', $rawCode, $matches) === 1) {
            $target = Str::lower($matches[1][0]) === 'w' ? 'wall' : 'album';
            $rawCode = substr($matches[1], 1);
        }

        $normalizedCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $rawCode) ?? '');

        $this->merge([
            'code' => $normalizedCode,
            'target' => in_array($target, ['album', 'wall'], true) ? $target : 'album',
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'alpha_num', 'size:4'],
            'target' => ['required', 'string', 'in:album,wall'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Enter the album or wall code from the QR card or short link.',
            'code.alpha_num' => 'Use only the letters and numbers from the access code.',
            'code.size' => 'Codes use 4 letters or numbers. Fill all 4 boxes and we will open it.',
        ];
    }

    public function normalizedCode(): string
    {
        return (string) $this->validated('code');
    }

    public function target(): string
    {
        return (string) $this->validated('target');
    }
}
