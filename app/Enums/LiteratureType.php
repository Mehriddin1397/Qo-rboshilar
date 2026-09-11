<?php

namespace App\Enums;

enum LiteratureType: string
{
    case Book = 'book';
    case Article = 'article';
    case Research = 'research';
    case Archive = 'archive';
    case Newspaper = 'newspaper';
    case Journal = 'journal';
    case Memoir = 'memoir';
    case Dissertation = 'dissertation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Book => 'Kitob',
            self::Article => 'Maqola',
            self::Research => 'Ilmiy tadqiqot',
            self::Archive => 'Arxiv hujjati',
            self::Newspaper => 'Gazeta',
            self::Journal => 'Jurnal',
            self::Memoir => 'Xotira',
            self::Dissertation => 'Dissertatsiya',
            self::Other => 'Boshqa',
        };
    }
}
