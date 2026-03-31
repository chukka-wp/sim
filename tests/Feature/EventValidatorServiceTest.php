<?php

use App\Services\EventValidatorService;

beforeEach(function () {
    $this->validator = new EventValidatorService;
    $this->homeTeamId = '01965a00-0002-7000-8000-000000000001';
    $this->awayTeamId = '01965a00-0002-7000-8000-000000000002';
    $this->homePlayerIds = ['01965a00-0003-7000-8000-000000000101'];
    $this->awayPlayerIds = ['01965a00-0003-7000-8000-000000000201'];
});

it('accepts valid events', function () {
    $events = [
        [
            'type' => 'match_start',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:00Z',
            'payload' => [],
        ],
        [
            'type' => 'period_start',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:01Z',
            'payload' => [],
        ],
    ];

    $result = $this->validator->validate(
        $events,
        $this->homePlayerIds,
        $this->awayPlayerIds,
        $this->homeTeamId,
        $this->awayTeamId,
    );

    expect($result->totalValid())->toBe(2);
    expect($result->totalSkipped())->toBe(0);
});

it('skips events with unknown type', function () {
    $events = [
        [
            'type' => 'not_a_real_event',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:00Z',
            'payload' => [],
        ],
    ];

    $result = $this->validator->validate(
        $events,
        $this->homePlayerIds,
        $this->awayPlayerIds,
        $this->homeTeamId,
        $this->awayTeamId,
    );

    expect($result->totalValid())->toBe(0);
    expect($result->totalSkipped())->toBe(1);
    expect($result->skippedEvents[0]['reason'])->toContain('Unknown event type');
});

it('skips events with missing period', function () {
    $events = [
        [
            'type' => 'match_start',
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:00Z',
            'payload' => [],
        ],
    ];

    $result = $this->validator->validate(
        $events,
        $this->homePlayerIds,
        $this->awayPlayerIds,
        $this->homeTeamId,
        $this->awayTeamId,
    );

    expect($result->totalValid())->toBe(0);
    expect($result->totalSkipped())->toBe(1);
});

it('skips events with invalid team id in payload', function () {
    $events = [
        [
            'type' => 'swim_off',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:00Z',
            'payload' => ['winning_team_id' => 'invalid-team-id'],
        ],
    ];

    $result = $this->validator->validate(
        $events,
        $this->homePlayerIds,
        $this->awayPlayerIds,
        $this->homeTeamId,
        $this->awayTeamId,
    );

    expect($result->totalSkipped())->toBe(1);
    expect($result->skippedEvents[0]['reason'])->toContain('Invalid winning_team_id');
});

it('handles mixed valid and invalid events', function () {
    $events = [
        [
            'type' => 'match_start',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:00Z',
            'payload' => [],
        ],
        [
            'type' => 'bogus_type',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:01Z',
            'payload' => [],
        ],
        [
            'type' => 'period_start',
            'period' => 1,
            'period_clock_seconds' => 480,
            'recorded_at' => '2025-03-15T10:00:02Z',
            'payload' => [],
        ],
    ];

    $result = $this->validator->validate(
        $events,
        $this->homePlayerIds,
        $this->awayPlayerIds,
        $this->homeTeamId,
        $this->awayTeamId,
    );

    expect($result->totalValid())->toBe(2);
    expect($result->totalSkipped())->toBe(1);
});
