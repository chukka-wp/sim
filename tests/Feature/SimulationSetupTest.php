<?php

use App\Enums\SimulationStatus;
use App\Jobs\GenerateSimulation;
use App\Models\SimulationSession;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Illuminate\Support\Facades\Queue;

it('loads the setup page with rule sets and presets', function () {
    $this->seed();

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Simulation/Setup')
        ->has('ruleSets')
        ->has('presets')
        ->has('models')
    );
});

it('has bundled rule sets available', function () {
    $this->seed();

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->has('ruleSets', 5)
    );
});

it('creates a session and dispatches generation job', function () {
    $this->seed();
    Queue::fake();

    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();

    $response = $this->post('/simulation', [
        'rule_set_id' => $ruleSet->id,
        'scenario_preset' => 'routine',
        'scenario_prompt' => 'A competitive match',
        'model_name' => 'claude-sonnet-4-5-20250514',
        'auto_play' => true,
    ]);

    $session = SimulationSession::first();

    expect($session)->not->toBeNull();
    expect($session->status)->toBe(SimulationStatus::Pending);
    expect($session->scenario_preset)->toBe('routine');

    $response->assertRedirect("/simulation/{$session->id}");

    Queue::assertPushed(GenerateSimulation::class, function ($job) use ($session) {
        return $job->uniqueId() === $session->id;
    });
});
