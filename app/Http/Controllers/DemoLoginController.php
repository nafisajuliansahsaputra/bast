<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(
            config('bast.demo.enabled') === true,
            404,
        );

        $email = config('bast.demo.email');

        abort_unless(
            is_string($email)
                && trim($email) !== '',
            404,
        );

        $user = User::query()
            ->with('role')
            ->where('email', $email)
            ->first();

        abort_unless(
            $user !== null
                && $user->isActive()
                && $user->hasVerifiedEmail()
                && $user->isAdmin(),
            503,
            'Akun demo belum siap digunakan.',
        );

        Auth::guard('web')->login(
            $user,
            false,
        );

        $request->session()->regenerate();

        return to_route('dashboard');
    }
}
