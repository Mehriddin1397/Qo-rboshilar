<?php

namespace App\Services;

use App\Models\HistoricalImage;
use App\Models\Qorboshi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class QorboshiMediaService
{
    private const DISK = 'public';

    /**
     * Portretni saqlaydi va eskisini (bo'lsa) o'chiradi. Laravel `store()` xavfsiz,
     * tasodifiy generatsiya qilingan fayl nomidan foydalanadi (user-supplied nom
     * hech qachon fayl tizimiga to'g'ridan-to'g'ri yozilmaydi).
     */
    public function storePortrait(Qorboshi $qorboshi, UploadedFile $file): string
    {
        if ($qorboshi->portrait_path) {
            Storage::disk(self::DISK)->delete($qorboshi->portrait_path);
        }

        return $file->store('qorboshilar/portraits', self::DISK);
    }

    public function deletePortrait(Qorboshi $qorboshi): void
    {
        if ($qorboshi->portrait_path) {
            Storage::disk(self::DISK)->delete($qorboshi->portrait_path);
        }
    }

    /**
     * @param  array{caption?: string, source?: string, source_url?: string, copyright?: string, year?: int, alt_text?: string}  $meta
     */
    public function addGalleryImage(Qorboshi $qorboshi, UploadedFile $file, array $meta = []): HistoricalImage
    {
        $path = $file->store('qorboshilar/gallery', self::DISK);

        return $qorboshi->images()->create([
            'file_path' => $path,
            ...$meta,
        ]);
    }

    public function deleteGalleryImage(HistoricalImage $image): void
    {
        Storage::disk(self::DISK)->delete($image->file_path);
        $image->delete();
    }

    /**
     * Qorboshi butunlay o'chirilishidan oldin barcha bog'liq fayllarni tozalaydi.
     * DB qatorlari uchun FK cascade (`historical_images.qorboshi_id`) allaqachon
     * migratsiyada sozlangan, lekin cascade faqat qatorlarni o'chiradi — jismoniy
     * fayllarni emas, shuning uchun bu yerda alohida tozalanadi.
     */
    public function deleteAllMedia(Qorboshi $qorboshi): void
    {
        $this->deletePortrait($qorboshi);

        foreach ($qorboshi->images as $image) {
            Storage::disk(self::DISK)->delete($image->file_path);
        }
    }
}
