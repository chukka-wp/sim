<?php

namespace App\Services;

use ChukkaWp\ChukkaSpec\Models\Player;
use Database\Seeders\SimTeamsSeeder;

class CloudBootstrapService
{
    public function __construct(
        private readonly CloudApiClient $cloudApi,
    ) {}

    public function isBootstrapped(): bool
    {
        $response = $this->cloudApi->getBootstrapStatus();

        return $response['bootstrapped'] ?? false;
    }

    public function bootstrap(): array
    {
        return $this->cloudApi->postBootstrap($this->buildPayload());
    }

    /** @return array{clubs: array<array{id: string, name: string, short_name: string|null, primary_colour: string|null, teams: array, players: array}>} */
    private function buildPayload(): array
    {
        return [
            'clubs' => [
                $this->buildClub(
                    id: SimTeamsSeeder::CENTRAL_CLUB_ID,
                    name: 'Central Newcastle Water Polo Club',
                    shortName: 'Central',
                    colour: '#003087',
                    teamId: SimTeamsSeeder::CENTRAL_TEAM_ID,
                    teamName: 'Central Newcastle',
                    teamShortName: 'Central',
                ),
                $this->buildClub(
                    id: SimTeamsSeeder::EASTS_CLUB_ID,
                    name: 'Easts Water Polo Club',
                    shortName: 'Easts',
                    colour: '#ffffff',
                    teamId: SimTeamsSeeder::EASTS_TEAM_ID,
                    teamName: 'Easts',
                    teamShortName: 'Easts',
                ),
            ],
        ];
    }

    private function buildClub(
        string $id,
        string $name,
        string $shortName,
        string $colour,
        string $teamId,
        string $teamName,
        string $teamShortName,
    ): array {
        $players = Player::where('club_id', $id)
            ->orderBy('preferred_cap_number')
            ->get()
            ->map(fn (Player $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'preferred_cap_number' => $p->preferred_cap_number,
                'is_goalkeeper' => $p->is_goalkeeper,
            ])
            ->values()
            ->all();

        return [
            'id' => $id,
            'name' => $name,
            'short_name' => $shortName,
            'primary_colour' => $colour,
            'teams' => [
                [
                    'id' => $teamId,
                    'name' => $teamName,
                    'short_name' => $teamShortName,
                    'gender' => 'male',
                    'age_group' => 'Open',
                ],
            ],
            'players' => $players,
        ];
    }
}
