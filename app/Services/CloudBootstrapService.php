<?php

namespace App\Services;

use ChukkaWp\ChukkaSpec\Models\Club;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SimTeamsSeeder;
use Illuminate\Support\Facades\Artisan;

class CloudBootstrapService
{
    /** Check if local sim database has been seeded with teams, players, and rule sets. */
    public function isBootstrapped(): bool
    {
        return Club::where('id', SimTeamsSeeder::CENTRAL_CLUB_ID)->exists()
            && Club::where('id', SimTeamsSeeder::EASTS_CLUB_ID)->exists()
            && RuleSet::where('is_bundled', true)->exists();
    }

    /** Seed the local database with teams, players, and rule sets. */
    public function bootstrap(): void
    {
        Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--no-interaction' => true,
        ]);
    }
}
