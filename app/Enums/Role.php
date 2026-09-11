<?php

namespace App\Enums;

enum Role: string
{
    case User = 'user';
    case Editor = 'editor';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Foydalanuvchi',
            self::Editor => 'Muharrir',
            self::Admin => 'Administrator',
        };
    }
}
