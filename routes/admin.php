<?php

use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HistoricalMapLayerController;
use App\Http\Controllers\Admin\HistoricalRegionController;
use App\Http\Controllers\Admin\LiteratureController;
use App\Http\Controllers\Admin\MapMarkerController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\QorboshiController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SourceReferenceController;
use App\Http\Controllers\Admin\TimelineEventController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UzgolonController;
use App\Http\Controllers\Admin\VideoController;
use Illuminate\Support\Facades\Route;

// Faza 6 — Admin Skeleton: standart holat "admin" roli. Faza 10'da Bloglar/Kommentlar
// moderatsiyasi 'role:admin,editor'ga kengaytirildi (pastga qarang) — shu sabab tashqi
// guruh endi faqat 'auth'ni beradi, har bir sub-guruh o'z rolini alohida belgilaydi.
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('qorboshilar', QorboshiController::class)->parameters(['qorboshilar' => 'qorboshi']);
        Route::delete('/qorboshilar/{qorboshi}/images/{image}', [QorboshiController::class, 'destroyGalleryImage'])
            ->name('qorboshilar.images.destroy');

        Route::resource('qozgolonlar', UzgolonController::class)->parameters(['qozgolonlar' => 'qozgolon']);
        Route::delete('/qozgolonlar/{qozgolon}/images/{image}', [UzgolonController::class, 'destroyGalleryImage'])
            ->name('qozgolonlar.images.destroy');

        Route::resource('adabiyotlar', LiteratureController::class)->parameters(['adabiyotlar' => 'adabiyot']);
        Route::resource('videolar', VideoController::class)->parameters(['videolar' => 'video']);

        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        Route::resource('regions', RegionController::class)->parameters(['regions' => 'region']);
        Route::resource('map-markers', MapMarkerController::class)->parameters(['map-markers' => 'mapMarker']);
        Route::resource('periods', PeriodController::class)->parameters(['periods' => 'period']);
        Route::resource('source-references', SourceReferenceController::class)
            ->parameters(['source-references' => 'sourceReference']);

        Route::resource('historical-regions', HistoricalRegionController::class)
            ->parameters(['historical-regions' => 'historicalRegion']);

        Route::resource('historical-map-layers', HistoricalMapLayerController::class)
            ->parameters(['historical-map-layers' => 'historicalMapLayer']);

        Route::resource('timeline-events', TimelineEventController::class)
            ->parameters(['timeline-events' => 'timelineEvent']);

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    });

    // Bloglar: index/show/approve/reject — admin + editor (moderatsiya).
    // create/store/edit/update/destroy — faqat admin (to'liq kontent boshqaruvi,
    // BlogPolicy bilan mos: create/update/delete hali admin-only).
    Route::resource('bloglar', BlogController::class)
        ->parameters(['bloglar' => 'blog'])
        ->middlewareFor(['index', 'show'], 'role:admin,editor')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'role:admin');

    Route::middleware('role:admin,editor')->group(function () {
        Route::post('/bloglar/{blog}/approve', [BlogController::class, 'approve'])->name('bloglar.approve');
        Route::post('/bloglar/{blog}/reject', [BlogController::class, 'reject'])->name('bloglar.reject');

        Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
        Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('comments.show');
        Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
        Route::post('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });
});
