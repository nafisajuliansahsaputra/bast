<?php

namespace App\Http\Controllers;

use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Unit;
use Inertia\Inertia;
use Inertia\Response;

class MasterDataController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render(
            'master/index',
            [
                'bastTypes' => BastType::query()
                    ->withCount('basts')
                    ->orderBy('name')
                    ->get(),

                'departments' => Department::query()
                    ->withCount([
                        'users',
                        'basts',
                    ])
                    ->orderBy('name')
                    ->get(),

                'itemCategories' => ItemCategory::query()
                    ->withCount('bastItems')
                    ->orderBy('name')
                    ->get(),

                'units' => Unit::query()
                    ->withCount('bastItems')
                    ->orderBy('name')
                    ->get(),
            ],
        );
    }
}
