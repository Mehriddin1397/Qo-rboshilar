<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ListsPlaceholderEntities;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    use ListsPlaceholderEntities;

    public function index(): View
    {
        return $this->renderIndex(User::class, 'Foydalanuvchilar');
    }
}
