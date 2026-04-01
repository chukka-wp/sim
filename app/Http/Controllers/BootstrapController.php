<?php

namespace App\Http\Controllers;

use App\Services\CloudBootstrapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class BootstrapController extends Controller
{
    public function __construct(
        private readonly CloudBootstrapService $bootstrapService,
    ) {}

    public function show(): Response|RedirectResponse
    {
        if ($this->bootstrapService->isBootstrapped()) {
            return redirect()->route('simulation.setup');
        }

        return Inertia::render('Bootstrap', [
            'cloudUrl' => config('chukka.cloud_url'),
        ]);
    }

    public function store(): RedirectResponse
    {
        $this->bootstrapService->bootstrap();

        Cache::forget('sim:bootstrapped');

        return redirect()->route('simulation.setup');
    }
}
