<?php

namespace App\Providers;

use App\Auth\ChukkaIdProvider;
use App\Services\CloudApiClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CloudApiClient::class, fn () => new CloudApiClient(
            baseUrl: config('chukka.cloud_url'),
            apiKey: config('chukka.api_key') ?? '',
        ));
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuth();
    }

    protected function configureAuth(): void
    {
        if (config('chukka.auth_provider') !== 'passport') {
            return;
        }

        Socialite::extend('chukka-id', function () {
            $config = config('services.chukka_id');

            return Socialite::buildProvider(ChukkaIdProvider::class, [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect' => $config['redirect'],
            ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
