<?php

namespace App\Services;

use ChukkaWp\ChukkaSpec\Enums\EventType;
use ChukkaWp\ChukkaSpec\Models\Player;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Database\Seeders\SimTeamsSeeder;
use Illuminate\Support\Collection;

class ScenarioPromptBuilder
{
    /**
     * @param  Collection<int, Player>  $centralPlayers
     * @param  Collection<int, Player>  $eastsPlayers
     */
    public function buildSystemPrompt(RuleSet $ruleSet, Collection $centralPlayers, Collection $eastsPlayers): string
    {
        $eventTypes = $this->formatEventTypes();
        $ruleSetDescription = $this->formatRuleSet($ruleSet);
        $centralRoster = $this->formatRoster($centralPlayers, 'Central Newcastle', SimTeamsSeeder::CENTRAL_TEAM_ID);
        $eastsRoster = $this->formatRoster($eastsPlayers, 'Easts', SimTeamsSeeder::EASTS_TEAM_ID);

        return <<<PROMPT
        You are a water polo match event generator. You produce realistic match event sequences in the chukka-spec format.

        ## Output Format
        Return ONLY a JSON array of event objects. No prose, no markdown fences, no explanation. Just the raw JSON array.

        Each event object must have this exact structure:
        {
          "type": "<event_type_string>",
          "period": <integer>,
          "period_clock_seconds": <integer>,
          "recorded_at": "<ISO 8601 timestamp>",
          "payload": { ... }
        }

        ## Event Types
        {$eventTypes}

        ## Rule Set: {$ruleSet->name}
        {$ruleSetDescription}

        ## Teams & Rosters

        ### Home Team: Central Newcastle (Blue)
        Team ID: {$this->teamId('central')}
        {$centralRoster}

        ### Away Team: Easts (White)
        Team ID: {$this->teamId('easts')}
        {$eastsRoster}

        ## Match Structure Requirements
        - Start with match_start, then period_start for Q1
        - Each period begins with period_start and a swim_off event
        - Each period ends with period_end
        - Between Q2 and Q3: halftime_start then halftime_end
        - End with match_end (or shootout_start → shootout_shot(s) → shootout_end → match_end if drawn)
        - period_clock_seconds counts DOWN from period_duration (e.g. 480 → 0)
        - recorded_at timestamps should reflect realistic real-time progression, starting from the current time

        ## Water Polo Realism Guidelines
        - Typical match: 10–18 goals total, 3–8 exclusion fouls, 5–15 ordinary fouls per team
        - Goals come in bursts — teams often score 2-3 in quick succession
        - Exclusion fouls lead to 20-second man-up situations; roughly 40-50% convert to goals
        - possession_change events occur after goals, turnovers, free throws, period starts
        - possession_clock_reset events when possession changes or after specific game events
        - Each exclusion_foul should be followed by a personal_foul_recorded event
        - Players who reach the personal foul limit ({$ruleSet->personal_foul_limit}) get a foul_out event
        - Keep goal totals realistic — a 4× 8min match typically sees 10–18 goals
        - For the goal payload: home_score_after and away_score_after must be running totals

        ## Payload Rules
        - Events with no payload (match_start, period_start, period_end, halftime_start, halftime_end, match_end, match_abandoned, timeout_end, referee_timeout_start, referee_timeout_end, shootout_start, var_review_start): use empty object {}
        - All other events require the payload fields documented for their type
        - Always use valid team_id and player_id values from the rosters above
        PROMPT;
    }

    public function buildUserPrompt(string $preset, string $customPrompt = ''): string
    {
        if ($preset === 'free_text') {
            return $customPrompt;
        }

        $presetPrompt = $this->getPresetPrompt($preset);

        if ($customPrompt !== '') {
            return "{$presetPrompt}\n\nAdditional instructions: {$customPrompt}";
        }

        return $presetPrompt;
    }

    /** @return array<array{key: string, label: string, prompt: string}> */
    public static function presets(): array
    {
        return [
            ['key' => 'routine', 'label' => 'Routine match', 'prompt' => 'Competitive but straightforward. Central wins by 2–3 goals. Normal distribution of fouls and exclusions.'],
            ['key' => 'close', 'label' => 'Close match', 'prompt' => 'Goes to the wire — decided by a single goal in Q4. High foul count.'],
            ['key' => 'penalty_shootout', 'label' => 'Penalty shootout', 'prompt' => 'Scores level at full time. Shootout required. Central win the shootout.'],
            ['key' => 'foul_out', 'label' => 'Foul-out drama', 'prompt' => 'A key player reaches 3 personal fouls and is excluded for the game.'],
            ['key' => 'simultaneous_exclusions', 'label' => 'Simultaneous exclusions', 'prompt' => 'At least two simultaneous exclusion events during the match.'],
            ['key' => 'dominant', 'label' => 'Dominant performance', 'prompt' => 'One-sided match — Central wins by 6+ goals.'],
            ['key' => 'high_exclusion', 'label' => 'High exclusion game', 'prompt' => 'Rough match — high exclusion count, multiple players on 2 personal fouls.'],
            ['key' => 'goalkeeper_sub', 'label' => 'Goalkeeper substitution', 'prompt' => 'Goalkeeper substitution mid-match for Central Newcastle.'],
            ['key' => 'running_time', 'label' => 'Running time match', 'prompt' => 'Uses running clock rules — no possession clock, continuous period clock.'],
            ['key' => 'free_text', 'label' => 'Free text', 'prompt' => ''],
        ];
    }

    private function getPresetPrompt(string $preset): string
    {
        $presetMap = collect(self::presets())->keyBy('key');

        $found = $presetMap->get($preset);

        if (! $found || $found['prompt'] === '') {
            return 'Simulate a complete, competitive water polo match.';
        }

        return "Simulate a complete water polo match between Central Newcastle (Blue) and Easts (White). {$found['prompt']}";
    }

    private function formatEventTypes(): string
    {
        $lines = [];

        foreach (EventType::cases() as $type) {
            if ($type === EventType::Correction) {
                continue;
            }

            $hasPayload = $type->hasPayload() ? 'has payload' : 'no payload (empty {})';
            $lines[] = "- {$type->value}: {$type->label()} ({$hasPayload})";
        }

        return implode("\n", $lines);
    }

    private function formatRuleSet(RuleSet $ruleSet): string
    {
        return <<<RULES
        - Periods: {$ruleSet->periods}
        - Period duration: {$ruleSet->period_duration_seconds} seconds
        - Running time: {$this->bool($ruleSet->running_time)}
        - Possession clock: {$this->bool($ruleSet->possession_clock_enabled)}
        - Possession time: {$ruleSet->possession_time_seconds}s (standard), {$ruleSet->second_possession_time_seconds}s (reduced)
        - Exclusion duration: {$ruleSet->exclusion_duration_seconds}s
        - Personal foul limit: {$ruleSet->personal_foul_limit}
        - Foul limit enforced: {$this->bool($ruleSet->foul_limit_enforced)}
        - Timeouts per team: {$ruleSet->timeouts_per_team}
        - Players per team: {$ruleSet->players_per_team}
        - Max in water: {$ruleSet->max_players_in_water}
        RULES;
    }

    /** @param Collection<int, Player> $players */
    private function formatRoster(Collection $players, string $teamName, string $teamId): string
    {
        $lines = ["Roster for {$teamName} (team_id: {$teamId}):"];

        foreach ($players->sortBy('preferred_cap_number') as $player) {
            $role = $player->is_goalkeeper ? ' (goalkeeper)' : '';
            $lines[] = "  #{$player->preferred_cap_number} {$player->name} — player_id: {$player->id}{$role}";
        }

        return implode("\n", $lines);
    }

    private function teamId(string $team): string
    {
        return $team === 'central'
            ? SimTeamsSeeder::CENTRAL_TEAM_ID
            : SimTeamsSeeder::EASTS_TEAM_ID;
    }

    private function bool(bool $value): string
    {
        return $value ? 'yes' : 'no';
    }
}
