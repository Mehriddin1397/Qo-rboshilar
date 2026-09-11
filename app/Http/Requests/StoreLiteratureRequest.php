<?php

namespace App\Http\Requests;

use App\Enums\LiteratureStatus;
use App\Enums\LiteratureType;
use App\Models\Literature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLiteratureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Literature::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxYear = (int) date('Y');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('adabiyotlar', 'slug')->ignore($this->route('adabiyot')),
            ],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1000', "max:{$maxYear}"],
            'isbn' => ['nullable', 'string', 'max:32'],
            'type' => ['required', Rule::enum(LiteratureType::class)],
            'language' => ['nullable', 'string', 'max:64'],
            'description' => ['required', 'string'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['required', Rule::enum(LiteratureStatus::class)],
            'featured' => ['nullable', 'boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],

            'qorboshi_ids' => ['nullable', 'array'],
            'qorboshi_ids.*' => ['exists:qorboshilar,id'],
            'uzgolon_ids' => ['nullable', 'array'],
            'uzgolon_ids.*' => ['exists:uzgolonlar,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.unique' => "Bu slug allaqachon band.",
            'file.mimes' => "Fayl faqat PDF formatida bo'lishi kerak.",
        ];
    }
}
