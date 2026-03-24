<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportEventGuestPartiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'import_text' => ['nullable', 'string', 'required_without:import_file', 'max:25000'],
            'import_file' => ['nullable', 'file', 'required_without:import_text', 'mimes:csv,txt', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'import_text.required_without' => 'Paste a guest list or choose a file to import.',
            'import_file.required_without' => 'Choose a file or paste a guest list to import.',
        ];
    }

    public function importContents(): string
    {
        /** @var UploadedFile|null $file */
        $file = $this->file('import_file');

        if ($file instanceof UploadedFile) {
            return trim((string) file_get_contents($file->getRealPath()));
        }

        return trim((string) $this->input('import_text'));
    }
}
