<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

/**
 * Faza 6 (Admin Skeleton) uchun: har bir entity ro'yxat sahifasini bir xil,
 * generic ko'rinishda ko'rsatadi (real DB ma'lumoti, lekin create/edit/delete yo'q).
 * Har bir entityning CRUD'i keyingi bosqichda shu controllerlar ichiga qo'shiladi.
 */
trait ListsPlaceholderEntities
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function renderIndex(string $modelClass, string $title): View
    {
        $items = $modelClass::query()->latest()->paginate(15);

        return view('admin.entities.index', [
            'title' => $title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => $title],
            ],
            'items' => $items,
        ]);
    }
}
