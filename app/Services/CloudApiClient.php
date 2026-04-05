<?php

namespace App\Services;

use App\Exceptions\CloudApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CloudApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    /**
     * Create a match in cloud using API key auth.
     *
     * @return array{id: string, owner_token: string}
     */
    public function createMatch(
        string $ruleSetId,
        string $homeTeamName,
        string $awayTeamName,
        ?string $homeExternalTeamId = null,
        ?string $awayExternalTeamId = null,
        array $options = [],
    ): array {
        $response = $this->apiKeyRequest()
            ->post('/api/v1/matches', array_merge([
                'rule_set_id' => $ruleSetId,
                'home_team_name' => $homeTeamName,
                'away_team_name' => $awayTeamName,
                'home_external_team_id' => $homeExternalTeamId,
                'away_external_team_id' => $awayExternalTeamId,
            ], $options))
            ->json();

        $matchData = $response['data'] ?? null;
        $ownerToken = $response['owner_token'] ?? null;

        if (! $matchData || ! $ownerToken) {
            throw new CloudApiException('Failed to create match — missing data or owner_token');
        }

        return [
            ...$matchData,
            'owner_token' => $ownerToken,
        ];
    }

    /**
     * Set the roster for a match using owner token auth.
     *
     * @param  array<array{side: string, cap_number: int, player_name: string, role: string, is_starting: bool, external_player_id: string|null}>  $entries
     */
    public function setRoster(string $matchId, array $entries, string $ownerToken): void
    {
        $this->tokenRequest($ownerToken)
            ->post("/api/v1/matches/{$matchId}/roster", ['entries' => $entries])
            ->throw();
    }

    /** Generate a scorer token for a match using owner token auth. */
    public function generateScorerToken(string $matchId, string $ownerToken): string
    {
        $response = $this->tokenRequest($ownerToken)
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

    private function apiKeyRequest(): PendingRequest
    {
        if (! $this->apiKey) {
            throw new CloudApiException('CHUKKA_API_KEY is not configured');
        }

        return $this->baseRequest()->withToken($this->apiKey);
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
