<?php

namespace App\Http\Requests;

use App\Models\Blog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "Mening bloglarim" — oddiy ro'yxatdan o'tgan foydalanuvchi uchun. Ataylab
 * `status` va `author_id` maydonlarini QABUL QILMAYDI — bular har doim
 * BlogService::createAsAuthor()da serverda majburan belgilanadi (Draft +
 * joriy user), shuning uchun foydalanuvchi hech qachon o'zini boshqa muallif
 * sifatida ko'rsata olmaydi yoki statusni to'g'ridan-to'g'ri "approved"ga
 * o'rnata olmaydi (buning uchun alohida `submit` amali va Editor/Admin
 * tasdig'i kerak).
 */
class StoreMyBlogRequest extends FormRequest
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
                Rule::unique('bloglar', 'slug')->ignore($this->route('blogim')),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],

            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],

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
        ];
    }
}
