<?php

namespace App\Services;

use App\Exceptions\CloudApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CloudApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $managerToken = '',
    ) {}

    public function createMatch(string $ruleSetId, string $homeTeamId, string $awayTeamId, array $options = []): array
    {
        return $this->managerRequest()
            ->post('/api/v1/matches', array_merge([
                'rule_set_id' => $ruleSetId,
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
            ], $options))
            ->json('data') ?? throw new CloudApiException('Failed to create match');
    }

    /** @param array<array{player_id: string, team_id: string, cap_number: int, is_starting: bool, role: string}> $entries */
    public function setRoster(string $matchId, array $entries): void
    {
        $this->managerRequest()
            ->post("/api/v1/matches/{$matchId}/roster", ['entries' => $entries])
            ->throw();
    }

    public function generateScorerToken(string $matchId): string
    {
        $response = $this->managerRequest()
            ->post("/api/v1/matches/{$matchId}/scorer-token");

        return $response->json('token') ?? throw new CloudApiException('Failed to generate scorer token');
    }

    public function postEvent(string $matchId, array $eventData, string $scorerToken): array
    {
        return $this->tokenRequest($scorerToken)
            ->post("/api/v1/matches/{$matchId}/events", $eventData)
            ->json('data') ?? [];
    }

    public function postEventBatch(string $matchId, array $events, string $scorerToken): array
    {
        return $this->tokenRequest($scorerToken)
            ->post("/api/v1/matches/{$matchId}/events/batch", ['events' => $events])
            ->json() ?? [];
    }

    public function getMatchState(string $matchId): array
    {
        return $this->publicRequest()
            ->get("/api/v1/matches/{$matchId}/state")
            ->json('data') ?? [];
    }

    public function getMatchEvents(string $matchId): array
    {
        return $this->publicRequest()
            ->get("/api/v1/matches/{$matchId}/events")
            ->json('data') ?? [];
    }

    private function managerRequest(): PendingRequest
    {
        return $this->baseRequest()->withToken($this->managerToken);
    }

    private function tokenRequest(string $token): PendingRequest
    {
        return $this->baseRequest()->withToken($token);
    }

    private function publicRequest(): PendingRequest
    {
        return $this->baseRequest();
    }

    private function baseRequest(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->throw(function ($response) {
                throw new CloudApiException(
                    "Cloud API error: {$response->status()}",
                    $response->status(),
                    $response->json() ?? [],
                );
            });
    }
}
