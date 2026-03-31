<?php

namespace Database\Seeders;

use ChukkaWp\ChukkaSpec\Models\Player;
use ChukkaWp\ChukkaSpec\Models\TeamMembership;
use Illuminate\Database\Seeder;

class SimRostersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTeamRoster(
            SimTeamsSeeder::CENTRAL_CLUB_ID,
            SimTeamsSeeder::CENTRAL_TEAM_ID,
            $this->centralPlayers(),
        );

        $this->seedTeamRoster(
            SimTeamsSeeder::EASTS_CLUB_ID,
            SimTeamsSeeder::EASTS_TEAM_ID,
            $this->eastsPlayers(),
        );
    }

    /** @param array<array{id: string, name: string, preferred_cap_number: int, is_goalkeeper: bool}> $players */
    private function seedTeamRoster(string $clubId, string $teamId, array $players): void
    {
        foreach ($players as $player) {
            Player::create([
                'id' => $player['id'],
                'club_id' => $clubId,
                'name' => $player['name'],
                'preferred_cap_number' => $player['preferred_cap_number'],
                'is_goalkeeper' => $player['is_goalkeeper'],
            ]);

            TeamMembership::create([
                'player_id' => $player['id'],
                'team_id' => $teamId,
                'joined_at' => '2025-01-01',
            ]);
        }
    }

    /** @return array<array{id: string, name: string, preferred_cap_number: int, is_goalkeeper: bool}> */
    private function centralPlayers(): array
    {
        return [
            ['id' => '01965a00-0003-7000-8000-000000000101', 'name' => 'M. Chen', 'preferred_cap_number' => 1, 'is_goalkeeper' => true],
            ['id' => '01965a00-0003-7000-8000-000000000102', 'name' => 'T. Williams', 'preferred_cap_number' => 2, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000103', 'name' => 'J. Morrison', 'preferred_cap_number' => 3, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000104', 'name' => 'S. Patel', 'preferred_cap_number' => 4, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000105', 'name' => 'B. Thompson', 'preferred_cap_number' => 5, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000106', 'name' => 'D. Nguyen', 'preferred_cap_number' => 6, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000107', 'name' => 'C. Walsh', 'preferred_cap_number' => 7, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000108', 'name' => 'R. Kumar', 'preferred_cap_number' => 8, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000109', 'name' => 'A. Davidson', 'preferred_cap_number' => 9, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000110', 'name' => 'L. Harrison', 'preferred_cap_number' => 10, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000111', 'name' => 'M. O\'Brien', 'preferred_cap_number' => 11, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000112', 'name' => 'J. Fitzgerald', 'preferred_cap_number' => 12, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000113', 'name' => 'P. Santos', 'preferred_cap_number' => 13, 'is_goalkeeper' => true],
        ];
    }

    /** @return array<array{id: string, name: string, preferred_cap_number: int, is_goalkeeper: bool}> */
    private function eastsPlayers(): array
    {
        return [
            ['id' => '01965a00-0003-7000-8000-000000000201', 'name' => 'J. Cooper', 'preferred_cap_number' => 1, 'is_goalkeeper' => true],
            ['id' => '01965a00-0003-7000-8000-000000000202', 'name' => 'R. Mitchell', 'preferred_cap_number' => 2, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000203', 'name' => 'A. Brooks', 'preferred_cap_number' => 3, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000204', 'name' => 'K. Torres', 'preferred_cap_number' => 4, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000205', 'name' => 'D. Kelly', 'preferred_cap_number' => 5, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000206', 'name' => 'S. Wright', 'preferred_cap_number' => 6, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000207', 'name' => 'N. Park', 'preferred_cap_number' => 7, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000208', 'name' => 'M. Foster', 'preferred_cap_number' => 8, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000209', 'name' => 'T. Ryan', 'preferred_cap_number' => 9, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000210', 'name' => 'L. Murphy', 'preferred_cap_number' => 10, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000211', 'name' => 'C. Reeves', 'preferred_cap_number' => 11, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000212', 'name' => 'B. Gallagher', 'preferred_cap_number' => 12, 'is_goalkeeper' => false],
            ['id' => '01965a00-0003-7000-8000-000000000213', 'name' => 'E. Shaw', 'preferred_cap_number' => 13, 'is_goalkeeper' => true],
        ];
    }
}
