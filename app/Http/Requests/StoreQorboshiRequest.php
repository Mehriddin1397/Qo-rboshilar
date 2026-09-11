<?php

namespace App\Http\Requests;

use App\Enums\QorboshiStatus;
use App\Models\Qorboshi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQorboshiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Qorboshi::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minYear = 1000;
        $maxYear = (int) date('Y');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('qorboshilar', 'slug')->ignore($this->route('qorboshi')),
            ],
            'short_description' => ['required', 'string', 'max:2000'],
            'biography' => ['required', 'string'],
            'historical_context' => ['nullable', 'string'],

            'birth_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}"],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'death_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}", 'gte:birth_year'],
            'death_place' => ['nullable', 'string', 'max:255'],

            'active_from_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}"],
            'active_to_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}", 'gte:active_from_year'],

            'region_id' => ['nullable', 'exists:regions,id'],
            'status' => ['required', Rule::enum(QorboshiStatus::class)],
            'featured' => ['nullable', 'boolean'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],

            'portrait' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],

            'uzgolon_ids' => ['nullable', 'array'],
            'uzgolon_ids.*' => ['exists:uzgolonlar,id'],
            'literature_ids' => ['nullable', 'array'],
            'literature_ids.*' => ['exists:adabiyotlar,id'],
            'source_reference_ids' => ['nullable', 'array'],
            'source_reference_ids.*' => ['exists:source_references,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak (masalan: madaminbek).",
            'slug.unique' => "Bu slug allaqachon band — boshqa qo'rboshi tomonidan ishlatilmoqda.",
            'death_year.gte' => "Vafot yili tug'ilgan yildan oldin bo'lishi mumkin emas.",
            'active_to_year.gte' => "Faoliyat yakuni boshlanishidan oldin bo'lishi mumkin emas.",
        ];
    }
}
