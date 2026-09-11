<?php

namespace App\Policies;

use App\Enums\BlogStatus;
use App\Enums\Role;
use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Blog $blog): bool
    {
        if ($blog->status === BlogStatus::Approved) {
            return true;
        }

        return $user && (
            in_array($user->role, [Role::Admin, Role::Editor], strict: true)
            || $user->id === $blog->author_id
        );
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Blog $blog): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        return $user->id === $blog->author_id && $blog->status === BlogStatus::Draft;
    }

    public function delete(User $user, Blog $blog): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        return $user->id === $blog->author_id && $blog->status === BlogStatus::Draft;
    }

    public function moderate(User $user, Blog $blog): bool
    {
        return in_array($user->role, [Role::Editor, Role::Admin], strict: true);
    }

    public function submit(User $user, Blog $blog): bool
    {
        return $user->id === $blog->author_id && $blog->status === BlogStatus::Draft;
    }

    public function approve(User $user, Blog $blog): bool
    {
        return $this->moderate($user, $blog) && $blog->status === BlogStatus::Pending;
    }

    public function reject(User $user, Blog $blog): bool
    {
        return $this->moderate($user, $blog) && $blog->status === BlogStatus::Pending;
    }
}
