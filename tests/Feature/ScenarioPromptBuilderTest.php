<?php

use App\Services\ScenarioPromptBuilder;
use ChukkaWp\ChukkaSpec\Models\Player;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Database\Seeders\SimTeamsSeeder;

beforeEach(function () {
    $this->seed();
    $this->builder = new ScenarioPromptBuilder;
});

it('builds a system prompt with all event types', function () {
    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();
    $centralPlayers = Player::where('club_id', SimTeamsSeeder::CENTRAL_CLUB_ID)->get();
    $eastsPlayers = Player::where('club_id', SimTeamsSeeder::EASTS_CLUB_ID)->get();

    $prompt = $this->builder->buildSystemPrompt($ruleSet, $centralPlayers, $eastsPlayers);

    expect($prompt)->toContain('match_start');
    expect($prompt)->toContain('goal');
    expect($prompt)->toContain('exclusion_foul');
    expect($prompt)->toContain('shootout_shot');
    expect($prompt)->toContain('Central Newcastle');
    expect($prompt)->toContain('Easts');
    expect($prompt)->toContain('M. Chen');
    expect($prompt)->toContain('J. Cooper');
    expect($prompt)->toContain(SimTeamsSeeder::CENTRAL_TEAM_ID);
    expect($prompt)->toContain(SimTeamsSeeder::EASTS_TEAM_ID);
});

it('builds a system prompt with rule set parameters', function () {
    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();
    $centralPlayers = Player::where('club_id', SimTeamsSeeder::CENTRAL_CLUB_ID)->get();
    $eastsPlayers = Player::where('club_id', SimTeamsSeeder::EASTS_CLUB_ID)->get();

    $prompt = $this->builder->buildSystemPrompt($ruleSet, $centralPlayers, $eastsPlayers);

    expect($prompt)->toContain('480');
    expect($prompt)->toContain('Periods: 4');
});

it('builds user prompt from preset', function () {
    $prompt = $this->builder->buildUserPrompt('routine');

    expect($prompt)->toContain('Central Newcastle');
    expect($prompt)->toContain('Central wins by 2');
});

it('uses custom prompt for free text preset', function () {
    $prompt = $this->builder->buildUserPrompt('free_text', 'My custom scenario');

    expect($prompt)->toBe('My custom scenario');
});

it('returns all presets', function () {
    $presets = ScenarioPromptBuilder::presets();

    expect($presets)->toHaveCount(10);
    expect(collect($presets)->pluck('key'))->toContain('routine', 'close', 'penalty_shootout', 'free_text');
});
