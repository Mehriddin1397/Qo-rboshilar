<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LiteratureController;
use App\Http\Controllers\Public\MapController;
use App\Http\Controllers\Public\QorboshiController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TimelineEventController;
use App\Http\Controllers\Public\UzgolonController;
use App\Http\Controllers\Public\VideoController;
use App\Http\Controllers\User\BlogController as MyBlogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/qorboshilar', [QorboshiController::class, 'index'])->name('qorboshilar.index');
Route::get('/qorboshilar/{slug}', [QorboshiController::class, 'show'])->name('qorboshilar.show');

Route::get('/qozgolonlar', [UzgolonController::class, 'index'])->name('qozgolonlar.index');
Route::get('/qozgolonlar/{slug}', [UzgolonController::class, 'show'])->name('qozgolonlar.show');

Route::get('/adabiyotlar', [LiteratureController::class, 'index'])->name('adabiyotlar.index');
Route::get('/adabiyotlar/{slug}', [LiteratureController::class, 'show'])->name('adabiyotlar.show');

Route::get('/videolar', [VideoController::class, 'index'])->name('videolar.index');
Route::get('/videolar/{slug}', [VideoController::class, 'show'])->name('videolar.show');

Route::get('/bloglar', [BlogController::class, 'index'])->name('bloglar.index');
Route::get('/bloglar/{slug}', [BlogController::class, 'show'])->name('bloglar.show');

Route::get('/xarita', [MapController::class, 'index'])->name('xarita');

Route::get('/xronologiya', [TimelineEventController::class, 'index'])->name('xronologiya.index');
Route::get('/xronologiya/{slug}', [TimelineEventController::class, 'show'])->name('xronologiya.show');

Route::get('/qidiruv', [SearchController::class, 'index'])->name('search');

Route::get('/boglanish', [ContactController::class, 'index'])->name('contact');

Route::get('/biz-haqimizda', [AboutController::class, 'index'])->name('about');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/royxatdan-otish', [RegisteredUserController::class, 'create'])->name('register');
    // Production QA §7: ro'yxatdan o'tishda avval hech qanday rate limit yo'q
    // edi (login'da FormRequest ichida qo'lda RateLimiter bor, lekin register'da
    // yo'q edi — audit paytida topilgan bo'shliq, ommaviy spam-akkaunt yaratishga
    // yo'l ochardi).
    Route::post('/royxatdan-otish', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/kirish', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/kirish', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/chiqish', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('bloglarim')->name('bloglarim.')->group(function () {
        Route::get('/', [MyBlogController::class, 'index'])->name('index');
        Route::get('/create', [MyBlogController::class, 'create'])->name('create');
        Route::post('/', [MyBlogController::class, 'store'])->name('store');
        Route::get('/{blogim}/edit', [MyBlogController::class, 'edit'])->name('edit');
        Route::put('/{blogim}', [MyBlogController::class, 'update'])->name('update');
        Route::post('/{blogim}/submit', [MyBlogController::class, 'submit'])->name('submit');
        Route::delete('/{blogim}', [MyBlogController::class, 'destroy'])->name('destroy');
    });

    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('throttle:10,1');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

require __DIR__.'/admin.php';
