<?php

namespace App\Services;

use App\Models\Literature;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LiteratureMediaService
{
    private const DISK = 'public';

    public function storeCover(Literature $literature, UploadedFile $file): string
    {
        if ($literature->cover_path) {
            Storage::disk(self::DISK)->delete($literature->cover_path);
        }

        return $file->store('adabiyotlar/covers', self::DISK);
    }

    /**
     * PDF/hujjat fayli. Mualliflik huquqi bilan bog'liq materiallarni tekshirmasdan
     * yuklamaslik — bu qaror admin darajasida qabul qilinadi (bu servis faqat texnik
     * saqlashga javobgar).
     */
    public function storeFile(Literature $literature, UploadedFile $file): string
    {
        if ($literature->file_path) {
            Storage::disk(self::DISK)->delete($literature->file_path);
        }

        return $file->store('adabiyotlar/files', self::DISK);
    }

    public function deleteAllMedia(Literature $literature): void
    {
        if ($literature->cover_path) {
            Storage::disk(self::DISK)->delete($literature->cover_path);
        }

        if ($literature->file_path) {
            Storage::disk(self::DISK)->delete($literature->file_path);
        }
    }
}
