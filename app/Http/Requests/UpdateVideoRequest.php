<?php

namespace App\Http\Requests;

class UpdateVideoRequest extends StoreVideoRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('video')) ?? false;
    }
}
