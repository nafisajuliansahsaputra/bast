<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoIsReadOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
    ): Response {
        $user = $request->user();

        if (
            config('bast.demo.enabled') !== true
            || config('bast.demo.read_only') !== true
            || $user === null
            || $user->email !== config('bast.demo.email')
            || $request->isMethodSafe()
            || $request->routeIs('logout')
        ) {
            return $next($request);
        }

        abort(
            403,
            'Mode demo hanya digunakan untuk eksplorasi dan bersifat read-only.',
        );
    }
}
