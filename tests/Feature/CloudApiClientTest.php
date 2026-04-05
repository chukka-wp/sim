<?php

use App\Services\CloudApiClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->client = new CloudApiClient(
        baseUrl: 'http://cloud.test',
        apiKey: 'test-api-key',
    );
});

it('creates a match with api key auth', function () {
    Http::fake([
        'cloud.test/api/v1/matches' => Http::response([
            'data' => ['id' => 'match-123', 'status' => 'scheduled'],
            'owner_token' => 'owner-token-xyz',
        ]),
    ]);

    $result = $this->client->createMatch(
        ruleSetId: 'rule-set-id',
        homeTeamName: 'Central Newcastle',
        awayTeamName: 'Easts',
        homeExternalTeamId: 'home-ext-id',
        awayExternalTeamId: 'away-ext-id',
    );

    expect($result)->toHaveKey('id', 'match-123');
    expect($result)->toHaveKey('owner_token', 'owner-token-xyz');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://cloud.test/api/v1/matches'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-api-key')
            && $request['rule_set_id'] === 'rule-set-id'
            && $request['home_team_name'] === 'Central Newcastle'
            && $request['away_team_name'] === 'Easts';
    });
});

it('sets roster with owner token', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/roster' => Http::response([], 200),
    ]);

    $this->client->setRoster('match-123', [
        ['side' => 'home', 'cap_number' => 1, 'player_name' => 'M. Chen', 'role' => 'goalkeeper', 'is_starting' => true, 'external_player_id' => 'player-1'],
    ], 'owner-token-xyz');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://cloud.test/api/v1/matches/match-123/roster'
            && $request->hasHeader('Authorization', 'Bearer owner-token-xyz')
            && $request['entries'][0]['side'] === 'home';
    });
});

it('generates a scorer token with owner token', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/scorer-token' => Http::response([
            'token' => 'scorer-token-abc',
        ]),
    ]);

    $token = $this->client->generateScorerToken('match-123', 'owner-token-xyz');

    expect($token)->toBe('scorer-token-abc');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer owner-token-xyz');
    });
});

it('posts an event with scorer token', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/events' => Http::response([
            'data' => ['id' => 'event-1'],
        ]),
    ]);

    $result = $this->client->postEvent('match-123', [
        'type' => 'match_start',
        'period' => 1,
        'period_clock_seconds' => 480,
        'payload' => [],
    ], 'scorer-token-abc');

    expect($result)->toHaveKey('id', 'event-1');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer scorer-token-abc')
            && $request['type'] === 'match_start';
    });
});

it('gets match state', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/state' => Http::response([
            'data' => ['status' => 'in_progress', 'home_score' => 3],
        ]),
    ]);

    $state = $this->client->getMatchState('match-123');

    expect($state)->toHaveKey('status', 'in_progress');
    expect($state)->toHaveKey('home_score', 3);
});
