<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UzgolonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function view(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function update(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function delete(User $user): bool
    {
        return $user->role === Role::Admin;
    }
}
