<?php

namespace App\Http\Requests;

class UpdateQorboshiRequest extends StoreQorboshiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('qorboshi')) ?? false;
    }
}
