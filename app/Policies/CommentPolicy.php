<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::Editor, Role::Admin], strict: true);
    }

    public function view(User $user, Comment $comment): bool
    {
        return $this->viewAny($user) || $user->id === $comment->author_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->role === Role::Admin || $user->id === $comment->author_id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $user->role === Role::Admin || $user->id === $comment->author_id;
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return in_array($user->role, [Role::Editor, Role::Admin], strict: true);
    }
}
