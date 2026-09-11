<?php

namespace App\Http\Requests;

use App\Enums\SourceType;
use App\Models\SourceReference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSourceReferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SourceReference::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'author' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1000', 'max:'.((int) date('Y') + 1)],
            'url' => ['nullable', 'url', 'max:2048'],
            'page' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:2000'],
            'source_type' => ['required', Rule::enum(SourceType::class)],
            'literature_id' => ['nullable', 'exists:adabiyotlar,id'],
        ];
    }
}
