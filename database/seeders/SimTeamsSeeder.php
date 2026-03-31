<?php

namespace Database\Seeders;

use ChukkaWp\ChukkaSpec\Models\Club;
use ChukkaWp\ChukkaSpec\Models\Team;
use Illuminate\Database\Seeder;

class SimTeamsSeeder extends Seeder
{
    public const CENTRAL_CLUB_ID = '01965a00-0001-7000-8000-000000000001';

    public const CENTRAL_TEAM_ID = '01965a00-0002-7000-8000-000000000001';

    public const EASTS_CLUB_ID = '01965a00-0001-7000-8000-000000000002';

    public const EASTS_TEAM_ID = '01965a00-0002-7000-8000-000000000002';

    public function run(): void
    {
        $central = Club::create([
            'id' => self::CENTRAL_CLUB_ID,
            'name' => 'Central Newcastle Water Polo Club',
            'short_name' => 'Central',
            'primary_colour' => '#003087',
        ]);

        Team::create([
            'id' => self::CENTRAL_TEAM_ID,
            'club_id' => $central->id,
            'name' => 'Central Newcastle',
            'short_name' => 'Central',
            'gender' => 'male',
            'age_group' => 'Open',
        ]);

        $easts = Club::create([
            'id' => self::EASTS_CLUB_ID,
            'name' => 'Easts Water Polo Club',
            'short_name' => 'Easts',
            'primary_colour' => '#ffffff',
        ]);

        Team::create([
            'id' => self::EASTS_TEAM_ID,
            'club_id' => $easts->id,
            'name' => 'Easts',
            'short_name' => 'Easts',
            'gender' => 'male',
            'age_group' => 'Open',
        ]);
    }
}
