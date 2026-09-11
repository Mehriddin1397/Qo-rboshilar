<?php

namespace App\Http\Requests;

class UpdateUzgolonRequest extends StoreUzgolonRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('qozgolon')) ?? false;
    }
}
