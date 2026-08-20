<?php

use App\Models\BastType;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

function createAuthorizationRole(string $slug): Role
{
    return Role::query()->create([
        'name' => Str::headline($slug),
        'slug' => $slug,
        'description' => null,
        'is_active' => true,
    ]);
}

beforeEach(function () {
    Route::middleware(['web', 'auth', 'active'])
        ->get('/_test/active-user', fn () => response('OK'));

    Route::middleware([
        'web',
        'auth',
        'active',
        'role:super-admin,admin',
    ])->get('/_test/admin-access', fn () => response('OK'));
});

test('active user can pass active middleware', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/active-user')
        ->assertOk()
        ->assertSee('OK');
});

test('inactive user is logged out and redirected to login', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get('/_test/active-user')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('super admin can access admin protected route', function () {
    $role = createAuthorizationRole('super-admin');

    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get('/_test/admin-access')
        ->assertOk();
});

test('admin can access admin protected route', function () {
    $role = createAuthorizationRole('admin');

    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get('/_test/admin-access')
        ->assertOk();
});

test('staff cannot access admin protected route', function () {
    $role = createAuthorizationRole('staff');

    $user = User::factory()->create([
        'role_id' => $role->id,
    ]);

    $this->actingAs($user)
        ->get('/_test/admin-access')
        ->assertForbidden();
});

test('user without role cannot access role protected route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/admin-access')
        ->assertForbidden();
});

test('master data seeder creates base data without duplicates', function () {
    $this->seed(MasterDataSeeder::class);
    $this->seed(MasterDataSeeder::class);

    expect(BastType::query()->count())->toBe(5)
        ->and(Unit::query()->count())->toBe(9)
        ->and(ItemCategory::query()->count())->toBe(6);
});
