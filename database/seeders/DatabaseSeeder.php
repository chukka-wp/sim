<?php

namespace Database\Seeders;

use ChukkaWp\ChukkaSpec\Database\Seeders\RuleSetSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RuleSetSeeder::class,
            SimTeamsSeeder::class,
            SimRostersSeeder::class,
        ]);
    }
}
