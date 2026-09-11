<?php

namespace App\Http\Requests;

class UpdateLiteratureRequest extends StoreLiteratureRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('adabiyot')) ?? false;
    }
}
