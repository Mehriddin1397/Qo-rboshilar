<?php

namespace App\Enums;

enum CommentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Moderatsiyada',
            self::Approved => 'Tasdiqlangan',
            self::Rejected => 'Rad etilgan',
        };
    }
}
