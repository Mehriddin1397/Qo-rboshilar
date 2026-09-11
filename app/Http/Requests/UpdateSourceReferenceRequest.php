<?php

namespace App\Http\Requests;

class UpdateSourceReferenceRequest extends StoreSourceReferenceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('sourceReference')) ?? false;
    }
}
