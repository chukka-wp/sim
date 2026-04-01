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

        ## Payload Rules — CRITICAL
        - Events with no payload: use empty object {}
        - All other events MUST include exactly the required fields shown above — use the EXACT field names (e.g. winning_team_id not team_id for swim_off, outcome not saved for shot, new_value_seconds not possession_time for possession_clock_reset, offending_team_id not team_id for fouls)
        - Always use valid team_id and player_id values from the rosters above
        - For personal_foul_recorded: triggered_by_event_id can be "previous" as a placeholder
        - For possession_clock_reset: always include new_value_seconds (28 or 18), mode ("standard" or "reduced"), and reason
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
        return <<<'EVENTS'
        ### No payload (use empty object {})
        - match_start, period_start, period_end, halftime_start, halftime_end, match_end, match_abandoned
        - timeout_end, referee_timeout_start, referee_timeout_end, shootout_start, var_review_start

        ### Events with required payload fields

        **swim_off**: { winning_team_id: string }

        **goal**: { team_id, player_id (nullable), cap_number (nullable int), method: "field_goal"|"penalty_throw"|"own_goal" (nullable), home_score_after: int, away_score_after: int }

        **shot**: { team_id, player_id (nullable), cap_number (nullable int), outcome: "saved"|"missed"|"blocked" }

        **ordinary_foul**: { offending_team_id, offending_player_id (nullable), offending_cap_number (nullable int), foul_subtype (nullable), free_throw_team_id }

        **exclusion_foul**: { offending_team_id, offending_player_id, offending_cap_number: int, foul_subtype (nullable), is_also_penalty (nullable bool), exclusion_duration_seconds: int, personal_foul_recorded: bool }

        **exclusion_expiry**: { player_id, team_id, reason: "time_elapsed"|"goal_scored"|"team_regained_possession"|"free_throw_awarded" }

        **personal_foul_recorded**: { player_id, team_id, cap_number: int, foul_count_after: int, triggered_by_event_id: string }

        **foul_out**: { player_id, team_id, cap_number: int, foul_count: int, substitute_immediately: bool, enforced: bool }

        **penalty_foul**: { offending_team_id, offending_player_id (nullable), offending_cap_number (nullable int), foul_subtype (nullable), personal_foul_recorded: bool, also_excluded_for_game (nullable bool) }

        **penalty_throw_taken**: { shooting_team_id, shooter_player_id (nullable), shooter_cap_number (nullable int), outcome: "goal"|"miss"|"saved"|"rebound_in_play", home_score_after (nullable int), away_score_after (nullable int) }

        **possession_change**: { team_id, reason: "goal_scored"|"free_throw"|"turnover"|"exclusion_expiry"|"timeout_end"|"period_start" }

        **possession_clock_reset**: { team_id, new_value_seconds: int, mode: "standard"|"reduced", reason: "new_possession"|"shot_rebound_attacking"|"exclusion_foul"|"two_meter_throw"|"penalty_throw_no_change"|"neutral_throw"|"goal_throw"|"timeout_end" }

        **possession_clock_expiry**: { team_id, free_throw_team_id, period_clock_seconds_remaining: int }

        **timeout_start**: { team_id, timeouts_remaining_after: int }

        **free_throw**: { team_id, reason: "foul"|"out_of_bounds"|"clock_expiry" (nullable), taken_by_player_id (nullable), taken_by_cap_number (nullable int) }

        **goal_throw**: { team_id, taken_by_player_id (nullable), taken_by_cap_number (nullable int) }

        **two_meter_throw**: { team_id, side: "left"|"right", taken_by_player_id (nullable), taken_by_cap_number (nullable int) }

        **neutral_throw**: { reason: "simultaneous_foul"|"simultaneous_whistle"|"ball_in_obstruction"|"disputed_start", location (nullable) }

        **substitution**: { team_id, players_off: array, players_on: array, substitution_type: "flying"|"standard"|"bleeding" }

        **goalkeeper_substitution**: { team_id, outgoing_player_id, incoming_player_id, outgoing_cap_number: int, incoming_cap_number: int }

        **simultaneous_exclusion**: { home_player_id, home_cap_number: int, away_player_id, away_cap_number: int, possession_at_exclusion: "home"|"away"|"none", exclusion_duration_seconds: int }

        **violent_action_exclusion**: { offending_team_id, offending_player_id, offending_cap_number: int, substitute_eligible_after_seconds: int, penalty_throw_awarded: bool, personal_foul_recorded: bool }

        **misconduct_exclusion**: { offending_team_id, offending_player_id, offending_cap_number: int, substitute_immediately: bool }

        **yellow_card**: { team_id, issued_to: "head_coach"|"team_official"|"player", player_id (nullable), cap_number (nullable int) }

        **red_card**: { team_id, issued_to: "head_coach"|"team_official"|"player", player_id (nullable), cap_number (nullable int), preceded_by_yellow_card: bool }

        **injury_stoppage**: { player_id (nullable), team_id (nullable), cap_number (nullable int), max_stoppage_seconds: int, possession_team_id }

        **shootout_shot**: { team_id, player_id, cap_number: int, round: int, outcome: "goal"|"miss"|"saved", home_shootout_score_after: int, away_shootout_score_after: int }

        **shootout_end**: { winning_team_id, home_shootout_score: int, away_shootout_score: int, rounds_completed: int }

        **var_review_end**: { outcome: "goal_confirmed"|"goal_disallowed"|"no_change"|"violent_action_sanctioned", notes (nullable) }

        **coach_challenge**: { team_id, challenged_event_id (nullable), outcome: "successful"|"unsuccessful", challenges_remaining_after: int }
        EVENTS;
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
