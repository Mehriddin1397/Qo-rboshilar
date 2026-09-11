<?php

namespace App\Http\Requests;

class UpdateMyBlogRequest extends StoreMyBlogRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('blogim')) ?? false;
    }
}
