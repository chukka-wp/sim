<?php

use App\Exceptions\CloudApiException;
use App\Services\CloudApiClient;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->client = new CloudApiClient(
        baseUrl: 'http://cloud.test',
        managerToken: 'test-manager-token',
    );
});

it('creates a match', function () {
    Http::fake([
        'cloud.test/api/v1/matches' => Http::response([
            'data' => ['id' => 'match-123', 'status' => 'scheduled'],
        ]),
    ]);

    $result = $this->client->createMatch('rule-set-id', 'home-team-id', 'away-team-id');

    expect($result)->toHaveKey('id', 'match-123');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://cloud.test/api/v1/matches'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer test-manager-token')
            && $request['rule_set_id'] === 'rule-set-id';
    });
});

it('generates a scorer token', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/scorer-token' => Http::response([
            'token' => 'scorer-token-abc',
            'data' => [],
        ]),
    ]);

    $token = $this->client->generateScorerToken('match-123');

    expect($token)->toBe('scorer-token-abc');
});

it('posts an event with scorer token', function () {
    Http::fake([
        'cloud.test/api/v1/matches/match-123/events' => Http::response([
            'data' => ['id' => 'event-1'],
        ]),
    ]);

    $this->client->useScorerToken('scorer-token-abc');

    $result = $this->client->postEvent('match-123', [
        'type' => 'match_start',
        'period' => 1,
        'period_clock_seconds' => 480,
        'payload' => [],
    ]);

    expect($result)->toHaveKey('id', 'event-1');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer scorer-token-abc')
            && $request['type'] === 'match_start';
    });
});

it('throws when posting event without scorer token', function () {
    $this->client->postEvent('match-123', []);
})->throws(CloudApiException::class, 'Scorer token not set');

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
