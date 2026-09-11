<?php

namespace App\Http\Requests;

use App\Enums\ContentType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'commentable_type' => ['required', 'string', Rule::in(array_column(ContentType::cases(), 'value'))],
            'commentable_id' => ['required', 'integer'],
            'content' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * §25: parent kontent published bo'lishi server tomonda qayta tekshiriladi —
     * frontend validatsiyasiga ishonilmaydi.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->has('commentable_type') || $validator->errors()->has('commentable_id')) {
                return;
            }

            $type = ContentType::from($this->string('commentable_type')->toString());
            $modelClass = $type->model();

            $exists = $modelClass::query()->published()->whereKey($this->integer('commentable_id'))->exists();

            if (! $exists) {
                $validator->errors()->add('commentable_id', "Ushbu kontentga izoh qoldirib bo'lmaydi.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Izoh matnini kiriting.',
            'content.max' => "Izoh matni juda uzun (maksimal 5000 belgi).",
        ];
    }
}
