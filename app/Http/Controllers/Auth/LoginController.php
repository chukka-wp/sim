<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Login', [
            'idUrl' => config('services.chukka_id.url'),
        ]);
    }

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('chukka-id')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $socialUser = Socialite::driver('chukka-id')->user();

        $user = User::updateOrCreate(
            ['chukka_id' => $socialUser->getId()],
            [
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt(str()->random(32)),
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('simulation.setup'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function devLogin(User $user): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        Auth::login($user, remember: true);

        return redirect()->route('simulation.setup');
    }
}
