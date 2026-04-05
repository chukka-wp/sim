<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('chukka.auth_provider') !== 'passport') {
            return $next($request);
        }

        if (! $request->user()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
