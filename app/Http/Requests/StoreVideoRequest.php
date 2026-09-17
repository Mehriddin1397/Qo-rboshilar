<?php

namespace App\Http\Requests;

use App\Enums\VideoCategory;
use App\Enums\VideoStatus;
use App\Models\Video;
use App\Support\YoutubeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Video::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('videolar', 'slug')->ignore($this->route('video')),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::enum(VideoCategory::class)],
            'youtube_url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                if (! YoutubeUrl::isValid($value)) {
                    $fail("Bu YouTube havolasi noto'g'ri yoki qo'llab-quvvatlanmaydigan formatda. Qabul qilinadigan formatlar: youtube.com/watch?v=..., youtu.be/..., youtube.com/shorts/...");
                }
            }],
            'duration_seconds' => ['nullable', 'integer', 'min:1'],

            'qorboshi_id' => ['nullable', 'exists:qorboshilar,id'],
            'uzgolon_id' => ['nullable', 'exists:uzgolonlar,id'],
            'literature_id' => ['nullable', 'exists:adabiyotlar,id'],

            'status' => ['required', Rule::enum(VideoStatus::class)],
            'featured' => ['nullable', 'boolean'],

            'sources' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],

            'source_reference_ids' => ['nullable', 'array'],
            'source_reference_ids.*' => ['exists:source_references,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => "Bu slug allaqachon band.",
        ];
    }
}
