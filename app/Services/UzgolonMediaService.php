<?php

namespace App\Services;

use App\Models\HistoricalImage;
use App\Models\Uzgolon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UzgolonMediaService
{
    private const DISK = 'public';

    public function storeCover(Uzgolon $uzgolon, UploadedFile $file): string
    {
        if ($uzgolon->cover_image) {
            Storage::disk(self::DISK)->delete($uzgolon->cover_image);
        }

        return $file->store('uzgolonlar/covers', self::DISK);
    }

    /**
     * @param  array{caption?: string, source?: string, source_url?: string, copyright?: string, year?: int, alt_text?: string}  $meta
     */
    public function addGalleryImage(Uzgolon $uzgolon, UploadedFile $file, array $meta = []): HistoricalImage
    {
        $path = $file->store('uzgolonlar/gallery', self::DISK);

        return $uzgolon->images()->create([
            'file_path' => $path,
            ...$meta,
        ]);
    }

    public function deleteGalleryImage(HistoricalImage $image): void
    {
        Storage::disk(self::DISK)->delete($image->file_path);
        $image->delete();
    }

    public function deleteAllMedia(Uzgolon $uzgolon): void
    {
        if ($uzgolon->cover_image) {
            Storage::disk(self::DISK)->delete($uzgolon->cover_image);
        }

        foreach ($uzgolon->images as $image) {
            Storage::disk(self::DISK)->delete($image->file_path);
        }
    }
}
