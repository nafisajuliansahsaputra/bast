<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (
            ! $user instanceof User
            || $roles === []
            || ! $user->hasRole(...$roles)
        ) {
            abort(
                Response::HTTP_FORBIDDEN,
                'Anda tidak memiliki hak akses untuk membuka halaman ini.',
            );
        }

        return $next($request);
    }
}
