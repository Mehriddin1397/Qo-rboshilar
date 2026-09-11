<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BlogMediaService
{
    private const DISK = 'public';

    public function storeCover(Blog $blog, UploadedFile $file): string
    {
        if ($blog->cover_path) {
            Storage::disk(self::DISK)->delete($blog->cover_path);
        }

        return $file->store('bloglar/covers', self::DISK);
    }

    public function deleteAllMedia(Blog $blog): void
    {
        if ($blog->cover_path) {
            Storage::disk(self::DISK)->delete($blog->cover_path);
        }
    }
}
