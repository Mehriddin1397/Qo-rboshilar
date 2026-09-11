<?php

namespace App\Enums;

enum SourceType: string
{
    case Book = 'book';
    case Article = 'article';
    case Archive = 'archive';
    case Newspaper = 'newspaper';
    case Interview = 'interview';
    case Website = 'website';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Book => 'Kitob',
            self::Article => 'Maqola',
            self::Archive => 'Arxiv hujjati',
            self::Newspaper => 'Gazeta',
            self::Interview => 'Intervyu',
            self::Website => 'Veb-sayt',
            self::Other => 'Boshqa',
        };
    }
}
