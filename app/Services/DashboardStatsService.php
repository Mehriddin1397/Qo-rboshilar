<?php

namespace App\Services;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\User;
use App\Models\Uzgolon;
use App\Models\Video;

class DashboardStatsService
{
    /**
     * @return array<string, int>
     */
    public function get(): array
    {
        return [
            'qorboshilar' => Qorboshi::count(),
            'uzgolonlar' => Uzgolon::count(),
            'adabiyotlar' => Literature::count(),
            'videolar' => Video::count(),
            'bloglar' => Blog::count(),
            'users' => User::count(),
            'pending_bloglar' => Blog::where('status', BlogStatus::Pending)->count(),
            'pending_comments' => Comment::where('status', CommentStatus::Pending)->count(),
        ];
    }
}
