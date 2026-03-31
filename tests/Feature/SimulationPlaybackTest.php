<?php

use App\Enums\SimulationStatus;
use App\Models\SimulationSession;
use ChukkaWp\ChukkaSpec\Models\RuleSet;

beforeEach(function () {
    $this->seed();
    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();

    $this->session = SimulationSession::create([
        'rule_set_id' => $ruleSet->id,
        'scenario_preset' => 'routine',
        'scenario_prompt' => 'Test simulation',
        'model_name' => 'claude-sonnet-4-5-20250514',
        'status' => SimulationStatus::Generated,
        'generated_events' => [
            ['type' => 'match_start', 'period' => 1, 'period_clock_seconds' => 480, 'recorded_at' => '2025-03-15T10:00:00Z', 'payload' => []],
        ],
        'total_events' => 1,
    ]);
});

it('loads the playback page', function () {
    $response = $this->get("/simulation/{$this->session->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Simulation/Playback')
        ->has('session')
        ->has('cloudUrl')
    );
});

it('returns session state as json', function () {
    $response = $this->getJson("/simulation/{$this->session->id}/state");

    $response->assertOk();
    $response->assertJsonStructure([
        'id',
        'status',
        'current_event_index',
        'total_events',
        'skipped_events',
    ]);
    $response->assertJsonMissing(['events']);
});

it('updates speed multiplier', function () {
    $this->post("/simulation/{$this->session->id}/speed", ['speed' => '5'])
        ->assertRedirect();

    $this->session->refresh();
    expect((float) $this->session->speed_multiplier)->toBe(5.0);
});

it('pauses a playing session', function () {
    $this->session->update([
        'status' => SimulationStatus::Playing,
        'cloud_match_id' => 'test-match-id',
        'scorer_token' => 'test-token',
    ]);

    $this->post("/simulation/{$this->session->id}/pause")
        ->assertRedirect();

    $this->session->refresh();
    expect($this->session->status)->toBe(SimulationStatus::Paused);
});

it('stops a playing session', function () {
    $this->session->update([
        'status' => SimulationStatus::Playing,
        'cloud_match_id' => 'test-match-id',
        'scorer_token' => 'test-token',
    ]);

    $this->post("/simulation/{$this->session->id}/stop")
        ->assertRedirect();

    $this->session->refresh();
    expect($this->session->status)->toBe(SimulationStatus::Stopped);
});
