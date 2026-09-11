<?php

namespace App\Http\Requests;

use App\Enums\BlogStatus;
use App\Models\Blog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Faqat ADMIN panel uchun — status va muallifni to'liq boshqarish imkonini beradi.
 * Oddiy foydalanuvchi o'z blogini yaratish/tahrirlashda StoreMyBlogRequest /
 * UpdateMyBlogRequest ishlatadi (u yerda status/author_id umuman qabul qilinmaydi).
 */
class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Blog::class) ?? false;
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
                Rule::unique('bloglar', 'slug')->ignore($this->route('blog')),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'author_id' => ['required', 'exists:users,id'],
            'status' => ['required', Rule::enum(BlogStatus::class)],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],

            'qorboshi_ids' => ['nullable', 'array'],
            'qorboshi_ids.*' => ['exists:qorboshilar,id'],
            'uzgolon_ids' => ['nullable', 'array'],
            'uzgolon_ids.*' => ['exists:uzgolonlar,id'],
            'literature_ids' => ['nullable', 'array'],
            'literature_ids.*' => ['exists:adabiyotlar,id'],
            'video_ids' => ['nullable', 'array'],
            'video_ids.*' => ['exists:videolar,id'],
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
