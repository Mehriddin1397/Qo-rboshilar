<?php

namespace App\Http\Requests;

use App\Enums\RegionStatus;
use App\Models\Region;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Region::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('regions', 'name')->ignore($this->route('region')),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('regions', 'slug')->ignore($this->route('region')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(RegionStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => "Bu nom bilan hudud allaqachon mavjud.",
            'slug.regex' => "Slug faqat kichik harflar, raqamlar va chiziqchadan iborat bo'lishi kerak.",
            'slug.unique' => 'Bu slug allaqachon band.',
        ];
    }
}
