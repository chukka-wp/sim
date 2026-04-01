<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuth
{
    public function __construct(
        private readonly TokenService $tokenService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->tokenService->hasSession()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
