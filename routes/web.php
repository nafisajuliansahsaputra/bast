<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BastAttachmentController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\BastDocumentController;
use App\Http\Controllers\BastLifecycleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\BastTypeController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\Master\ItemCategoryController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserController;
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
        'bast/{bast}/complete',
        [
            BastLifecycleController::class,
            'complete',
        ],
    )->name('bast.complete');

    Route::post(
        'bast/{bast}/archive',
        [
            BastLifecycleController::class,
            'archive',
        ],
    )->name('bast.archive');

    Route::post(
        'bast/{bast}/restore',
        [
            BastLifecycleController::class,
            'restore',
        ],
    )->name('bast.restore');

    Route::get(
        'bast/{bast}/preview',
        [
            BastDocumentController::class,
            'preview',
        ],
    )->name('bast.preview');

    Route::get(
        'bast/{bast}/pdf',
        [
            BastDocumentController::class,
            'pdf',
        ],
    )->name('bast.pdf');

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

    Route::get(
        'archive',
        [
            ArchiveController::class,
            'index',
        ],
    )->name('archive.index');

    Route::middleware(
        'role:super-admin,admin',
    )->group(function () {
        Route::get(
            'master',
            MasterDataController::class,
        )->name('master.index');

        Route::post(
            'master/bast-types',
            [
                BastTypeController::class,
                'store',
            ],
        )->name('master.bast-types.store');

        Route::put(
            'master/bast-types/{bastType}',
            [
                BastTypeController::class,
                'update',
            ],
        )->name('master.bast-types.update');

        Route::patch(
            'master/bast-types/{bastType}/toggle',
            [
                BastTypeController::class,
                'toggle',
            ],
        )->name('master.bast-types.toggle');

        Route::post(
            'master/departments',
            [
                DepartmentController::class,
                'store',
            ],
        )->name('master.departments.store');

        Route::put(
            'master/departments/{department}',
            [
                DepartmentController::class,
                'update',
            ],
        )->name('master.departments.update');

        Route::patch(
            'master/departments/{department}/toggle',
            [
                DepartmentController::class,
                'toggle',
            ],
        )->name('master.departments.toggle');

        Route::post(
            'master/item-categories',
            [
                ItemCategoryController::class,
                'store',
            ],
        )->name('master.item-categories.store');

        Route::put(
            'master/item-categories/{itemCategory}',
            [
                ItemCategoryController::class,
                'update',
            ],
        )->name('master.item-categories.update');

        Route::patch(
            'master/item-categories/{itemCategory}/toggle',
            [
                ItemCategoryController::class,
                'toggle',
            ],
        )->name('master.item-categories.toggle');

        Route::post(
            'master/units',
            [
                UnitController::class,
                'store',
            ],
        )->name('master.units.store');

        Route::put(
            'master/units/{unit}',
            [
                UnitController::class,
                'update',
            ],
        )->name('master.units.update');

        Route::patch(
            'master/units/{unit}/toggle',
            [
                UnitController::class,
                'toggle',
            ],
        )->name('master.units.toggle');

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
        Route::get(
            'users',
            [
                UserController::class,
                'index',
            ],
        )->name('users.index');

        Route::post(
            'users',
            [
                UserController::class,
                'store',
            ],
        )->name('users.store');

        Route::get(
            'users/{user}',
            [
                UserController::class,
                'show',
            ],
        )->name('users.show');

        Route::put(
            'users/{user}',
            [
                UserController::class,
                'update',
            ],
        )->name('users.update');

        Route::patch(
            'users/{user}/status',
            [
                UserController::class,
                'toggleStatus',
            ],
        )->name('users.toggle-status');

        Route::post(
            'users/{user}/reset-password',
            [
                UserController::class,
                'resetPassword',
            ],
        )->name('users.reset-password');
    });
});

require __DIR__.'/settings.php';
