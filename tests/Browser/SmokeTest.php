<?php

use App\Enums\SimulationStatus;
use App\Models\SimulationSession;
use ChukkaWp\ChukkaSpec\Models\RuleSet;

it('has no smoke on all pages', function () {
    $this->seed();

    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();

    $session = SimulationSession::create([
        'rule_set_id' => $ruleSet->id,
        'scenario_preset' => 'routine',
        'scenario_prompt' => 'Test',
        'model_name' => 'claude-sonnet-4-5-20250514',
        'status' => SimulationStatus::Generated,
        'generated_events' => [
            ['type' => 'match_start', 'period' => 1, 'period_clock_seconds' => 480, 'recorded_at' => '2025-03-15T10:00:00Z', 'payload' => []],
        ],
        'total_events' => 1,
    ]);

    $pages = visit(['/', "/simulation/{$session->id}"]);

    $pages->assertNoSmoke();
});
