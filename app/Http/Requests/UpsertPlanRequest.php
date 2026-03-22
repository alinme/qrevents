<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertPlanRequest extends FormRequest
{
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
        $plan = $this->route('plan');

        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('plans', 'slug')->ignore($plan),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', 'size:3'],
            'price_cents' => ['required', 'integer', 'min:0', 'max:100000000'],
            'storage_limit_gb' => ['required', 'integer', 'min:1', 'max:10000'],
            'upload_limit' => ['required', 'integer', 'min:1', 'max:1000000'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'upload_window_days' => ['required', 'integer', 'min:1', 'max:365'],
            'customization_tier' => ['required', 'string', Rule::in(['basic', 'better', 'advanced'])],
            'video_max_duration_seconds' => ['required', 'integer', 'min:1', 'max:600'],
            'photo_max_size_mb' => ['required', 'integer', 'min:1', 'max:1024'],
            'video_max_size_mb' => ['required', 'integer', 'min:1', 'max:10240'],
            'download_all_enabled' => ['sometimes', 'boolean'],
            'moderation_tools_enabled' => ['sometimes', 'boolean'],
            'remove_app_branding' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.unique' => 'Choose a different package slug.',
            'currency.size' => 'Currency must use a 3-letter code like EUR or RON.',
            'storage_limit_gb.min' => 'Storage must be at least 1 GB.',
            'upload_limit.min' => 'Upload limit must be at least 1 item.',
            'video_max_duration_seconds.max' => 'Video length must stay under 600 seconds.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $slug = trim((string) $this->input('slug', ''));

        $this->merge([
            'name' => $name,
            'slug' => str($slug !== '' ? $slug : $name)->slug()->value(),
            'currency' => mb_strtoupper(trim((string) $this->input('currency', 'EUR'))),
            'customization_tier' => trim((string) $this->input('customization_tier', 'basic')),
            'download_all_enabled' => $this->boolean('download_all_enabled'),
            'moderation_tools_enabled' => $this->boolean('moderation_tools_enabled'),
            'remove_app_branding' => $this->boolean('remove_app_branding'),
            'is_active' => $this->boolean('is_active'),
            'is_default' => $this->boolean('is_default'),
        ]);
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->boolean('is_default') && ! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'A default package must stay active.');
                }
            },
        ];
    }
}
