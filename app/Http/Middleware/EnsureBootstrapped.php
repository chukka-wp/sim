<?php

namespace App\Http\Middleware;

use App\Services\CloudBootstrapService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureBootstrapped
{
    public function __construct(
        private readonly CloudBootstrapService $bootstrapService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $bootstrapped = Cache::remember('sim:bootstrapped', 300, function () {
            try {
                return $this->bootstrapService->isBootstrapped();
            } catch (\Exception) {
                return false;
            }
        });

        if (! $bootstrapped) {
            return redirect()->route('bootstrap.show');
        }

        return $next($request);
    }
}
