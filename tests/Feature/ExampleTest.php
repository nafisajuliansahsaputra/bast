<?php

use App\Models\User;

test('guest is redirected from home to login', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
});

test('authenticated user is redirected from home to dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});
