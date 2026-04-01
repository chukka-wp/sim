<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\CloudApiException;
use App\Http\Controllers\Controller;
use App\Services\CloudApiClient;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(
        private readonly CloudApiClient $cloudApi,
        private readonly TokenService $tokenService,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'cloudUrl' => config('chukka.cloud_url'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $response = $this->cloudApi->login(
                $request->input('email'),
                $request->input('password'),
            );

            $this->tokenService->storeSession(
                $response['token'],
                $response['user'],
            );

            $request->session()->regenerate();

            return redirect()->intended(route('simulation.setup'));
        } catch (CloudApiException) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $token = $this->tokenService->getToken();

        if ($token) {
            try {
                $this->cloudApi->logout($token);
            } catch (\Exception) {
                // Clear local session regardless
            }
        }

        $this->tokenService->clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
