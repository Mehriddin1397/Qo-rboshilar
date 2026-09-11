<?php

namespace App\Enums;

enum BlogStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Qoralama',
            self::Pending => 'Moderatsiyada',
            self::Approved => 'Tasdiqlangan',
            self::Rejected => 'Rad etilgan',
        };
    }
}
