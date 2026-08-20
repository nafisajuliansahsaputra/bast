<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)
        ->name('dashboard');

    Route::inertia('bast', 'module-placeholder', [
        'title' => 'Berita Acara',
        'description' => 'Modul pengelolaan Berita Acara Serah Terima sedang dipersiapkan.',
        'href' => '/bast',
    ])->name('bast.index');

    Route::inertia('bast/create', 'module-placeholder', [
        'title' => 'Buat Berita Acara',
        'description' => 'Form pembuatan Berita Acara Serah Terima akan tersedia pada tahap berikutnya.',
        'href' => '/bast/create',
    ])->name('bast.create');

    Route::inertia('archive', 'module-placeholder', [
        'title' => 'Arsip',
        'description' => 'Modul arsip dokumen Berita Acara Serah Terima sedang dipersiapkan.',
        'href' => '/archive',
    ])->name('archive.index');

    Route::middleware('role:super-admin,admin')->group(function () {
        Route::inertia('master', 'module-placeholder', [
            'title' => 'Data Master',
            'description' => 'Pengelolaan jenis BAST, kategori item, satuan, dan data master lainnya sedang dipersiapkan.',
            'href' => '/master',
        ])->name('master.index');

        Route::inertia('activity-logs', 'module-placeholder', [
            'title' => 'Activity Log',
            'description' => 'Riwayat aktivitas dan audit sistem sedang dipersiapkan.',
            'href' => '/activity-logs',
        ])->name('activity-logs.index');
    });

    Route::middleware('role:super-admin')->group(function () {
        Route::inertia('users', 'module-placeholder', [
            'title' => 'Pengguna',
            'description' => 'Modul pengelolaan akun pengguna dan hak akses sedang dipersiapkan.',
            'href' => '/users',
        ])->name('users.index');
    });
});

require __DIR__.'/settings.php';
