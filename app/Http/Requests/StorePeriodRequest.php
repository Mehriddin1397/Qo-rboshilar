<?php

namespace App\Http\Requests;

use App\Models\Period;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Period::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minYear = 1000;
        $maxYear = (int) date('Y');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('periods', 'name')->ignore($this->route('period')),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('periods', 'slug')->ignore($this->route('period')),
            ],
            'start_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}"],
            'end_year' => ['nullable', 'integer', "min:{$minYear}", "max:{$maxYear}", 'gte:start_year'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Bu nom bilan davr allaqachon mavjud.',
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => 'Bu slug allaqachon band.',
            'end_year.gte' => "Yakunlanish yili boshlanish yilidan oldin bo'lishi mumkin emas.",
        ];
    }
}
