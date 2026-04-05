<?php

namespace App\Services;

use App\Agents\MatchSimulationAgent;
use App\Enums\SimulationStatus;
use App\Jobs\PlaySimulation;
use App\Models\SimulationSession;
use ChukkaWp\ChukkaSpec\Models\Player;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Database\Seeders\SimTeamsSeeder;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;

class SimulationService
{
    public function __construct(
        private readonly CloudApiClient $cloudApi,
        private readonly ScenarioPromptBuilder $promptBuilder,
        private readonly EventValidatorService $validator,
    ) {}

    public function createSession(
        string $ruleSetId,
        string $scenarioPreset,
        string $scenarioPrompt,
        string $modelName,
    ): SimulationSession {
        return SimulationSession::create([
            'rule_set_id' => $ruleSetId,
            'scenario_preset' => $scenarioPreset,
            'scenario_prompt' => $scenarioPrompt,
            'model_name' => $modelName,
            'status' => SimulationStatus::Pending,
        ]);
    }

    public function generate(SimulationSession $session): SimulationSession
    {
        $session->update(['status' => SimulationStatus::Generating]);

        try {
            $ruleSet = RuleSet::findOrFail($session->rule_set_id);
            $centralPlayers = Player::where('club_id', SimTeamsSeeder::CENTRAL_CLUB_ID)->get();
            $eastsPlayers = Player::where('club_id', SimTeamsSeeder::EASTS_CLUB_ID)->get();

            Log::info('[sim] Loaded data for generation', [
                'session_id' => $session->id,
                'rule_set' => $ruleSet->name,
                'model' => $session->model_name,
                'central_players' => $centralPlayers->count(),
                'easts_players' => $eastsPlayers->count(),
            ]);

            $agent = new MatchSimulationAgent($ruleSet, $centralPlayers, $eastsPlayers);

            $userPrompt = $this->promptBuilder->buildUserPrompt(
                $session->scenario_preset ?? 'routine',
                $session->scenario_prompt,
            );

            Log::info('[sim] Calling LLM...', [
                'session_id' => $session->id,
                'prompt_length' => strlen($userPrompt),
            ]);

            $response = $agent->prompt(
                prompt: $userPrompt,
                provider: Lab::Anthropic,
                model: $session->model_name,
            );

            Log::info('[sim] LLM response received', [
                'session_id' => $session->id,
                'raw_event_count' => count($response->structured['events'] ?? []),
            ]);

            $rawEvents = $response->structured['events'] ?? [];

            $result = $this->validator->validate(
                rawEvents: $rawEvents,
                homePlayerIds: $centralPlayers->pluck('id')->all(),
                awayPlayerIds: $eastsPlayers->pluck('id')->all(),
                homeTeamId: SimTeamsSeeder::CENTRAL_TEAM_ID,
                awayTeamId: SimTeamsSeeder::EASTS_TEAM_ID,
            );

            $session->update([
                'status' => SimulationStatus::Generated,
                'generated_events' => $result->validEvents,
                'total_events' => $result->totalValid(),
                'skipped_events' => $result->skippedEvents,
            ]);

            Log::info("[sim] Generated {$result->totalValid()} events, skipped {$result->totalSkipped()}", [
                'session_id' => $session->id,
            ]);
        } catch (\Throwable $e) {
            $session->update([
                'status' => SimulationStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            Log::error("[sim] Generation failed: {$e->getMessage()}", [
                'session_id' => $session->id,
                'exception' => $e,
            ]);
        }

        return $session->fresh();
    }

    public function setupCloudMatch(SimulationSession $session): SimulationSession
    {
        try {
            Log::info('[sim] Creating cloud match...', ['session_id' => $session->id]);

            $match = $this->cloudApi->createMatch(
                ruleSetId: $session->rule_set_id,
                homeTeamName: 'Central Newcastle',
                awayTeamName: 'Easts',
                homeExternalTeamId: SimTeamsSeeder::CENTRAL_TEAM_ID,
                awayExternalTeamId: SimTeamsSeeder::EASTS_TEAM_ID,
                options: [
                    'venue' => 'Simulation Arena',
                    'scheduled_at' => now()->toIso8601String(),
                ],
            );

            $matchId = $match['id'];
            $ownerToken = $match['owner_token'];

            $session->update([
                'cloud_match_id' => $matchId,
                'owner_token' => $ownerToken,
            ]);

            Log::info('[sim] Cloud match created, setting roster...', [
                'session_id' => $session->id,
                'cloud_match_id' => $matchId,
            ]);

            $rosterEntries = $this->buildRosterEntries();
            $this->cloudApi->setRoster($matchId, $rosterEntries, $ownerToken);

            Log::info('[sim] Roster set, generating scorer token...', ['session_id' => $session->id]);

            $scorerToken = $this->cloudApi->generateScorerToken($matchId, $ownerToken);
            $session->update(['scorer_token' => $scorerToken]);

            Log::info('[sim] Cloud setup complete', ['session_id' => $session->id]);
        } catch (\Throwable $e) {
            $session->update([
                'status' => SimulationStatus::Failed,
                'error_message' => "Cloud setup failed: {$e->getMessage()}",
            ]);

            Log::error("[sim] Cloud setup failed: {$e->getMessage()}", [
                'session_id' => $session->id,
                'exception' => $e,
            ]);
        }

        return $session->fresh();
    }

    public function startPlayback(SimulationSession $session): void
    {
        $session->update([
            'status' => SimulationStatus::Playing,
        ]);

        PlaySimulation::dispatch($session);
    }

    public function pause(SimulationSession $session): void
    {
        $session->update(['status' => SimulationStatus::Paused]);
    }

    public function resume(SimulationSession $session): void
    {
        $session->update(['status' => SimulationStatus::Playing]);

        PlaySimulation::dispatch($session);
    }

    public function stop(SimulationSession $session): void
    {
        $session->update(['status' => SimulationStatus::Stopped]);
    }

    public function setSpeed(SimulationSession $session, float $multiplier): void
    {
        $session->update(['speed_multiplier' => $multiplier]);
    }

    /** @return array<array{side: string, cap_number: int, player_name: string, role: string, is_starting: bool, external_player_id: string}> */
    private function buildRosterEntries(): array
    {
        $entries = [];

        $teams = [
            SimTeamsSeeder::CENTRAL_CLUB_ID => 'home',
            SimTeamsSeeder::EASTS_CLUB_ID => 'away',
        ];

        foreach ($teams as $clubId => $side) {
            $players = Player::where('club_id', $clubId)
                ->orderBy('preferred_cap_number')
                ->get();

            foreach ($players as $index => $player) {
                $role = match (true) {
                    $player->is_goalkeeper && $player->preferred_cap_number === 1 => 'goalkeeper',
                    $player->is_goalkeeper => 'substitute_goalkeeper',
                    default => 'field_player',
                };

                $entries[] = [
                    'side' => $side,
                    'cap_number' => $player->preferred_cap_number,
                    'player_name' => $player->name,
                    'role' => $role,
                    'is_starting' => $index < 7,
                    'external_player_id' => $player->id,
                ];
            }
        }

        return $entries;
    }
}
