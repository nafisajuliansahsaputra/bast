<?php

use App\Http\Controllers\BastAttachmentController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia(
    '/',
    'welcome',
)->name('home');

Route::middleware([
    'auth',
    'active',
    'verified',
])->group(function () {
    Route::get(
        'dashboard',
        DashboardController::class,
    )->name('dashboard');

    Route::post(
        'bast/{bast}/finalize',
        [
            BastController::class,
            'finalize',
        ],
    )->name('bast.finalize');

    Route::post(
        'bast/{bast}/attachments',
        [
            BastAttachmentController::class,
            'store',
        ],
    )->name('bast.attachments.store');

    Route::get(
        'bast/{bast}/attachments/{attachment}/download',
        [
            BastAttachmentController::class,
            'download',
        ],
    )->name('bast.attachments.download');

    Route::delete(
        'bast/{bast}/attachments/{attachment}',
        [
            BastAttachmentController::class,
            'destroy',
        ],
    )->name('bast.attachments.destroy');

    Route::resource(
        'bast',
        BastController::class,
    )->only([
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ]);

    Route::inertia(
        'archive',
        'module-placeholder',
        [
            'title' => 'Arsip',
            'description' => 'Modul arsip dokumen Berita Acara Serah Terima sedang dipersiapkan.',
            'href' => '/archive',
        ],
    )->name('archive.index');

    Route::middleware(
        'role:super-admin,admin',
    )->group(function () {
        Route::inertia(
            'master',
            'module-placeholder',
            [
                'title' => 'Data Master',
                'description' => 'Pengelolaan jenis BAST, kategori item, satuan, dan data master lainnya sedang dipersiapkan.',
                'href' => '/master',
            ],
        )->name('master.index');

        Route::inertia(
            'activity-logs',
            'module-placeholder',
            [
                'title' => 'Activity Log',
                'description' => 'Riwayat aktivitas dan audit sistem sedang dipersiapkan.',
                'href' => '/activity-logs',
            ],
        )->name('activity-logs.index');
    });

    Route::middleware(
        'role:super-admin',
    )->group(function () {
        Route::inertia(
            'users',
            'module-placeholder',
            [
                'title' => 'Pengguna',
                'description' => 'Modul pengelolaan akun pengguna dan hak akses sedang dipersiapkan.',
                'href' => '/users',
            ],
        )->name('users.index');
    });
});

require __DIR__.'/settings.php';
