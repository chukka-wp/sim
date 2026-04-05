<?php

namespace App\Http\Controllers;

use App\Enums\SimulationStatus;
use App\Jobs\GenerateSimulation;
use App\Models\SimulationSession;
use App\Services\EventPlayerService;
use App\Services\ScenarioPromptBuilder;
use App\Services\SimulationService;
use ChukkaWp\ChukkaSpec\Enums\EventType;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SimulationController extends Controller
{
    public function setup(): Response
    {
        return Inertia::render('Simulation/Setup', [
            'ruleSets' => RuleSet::where('is_bundled', true)->get(['id', 'name', 'periods', 'period_duration_seconds', 'running_time', 'possession_clock_enabled']),
            'presets' => ScenarioPromptBuilder::presets(),
            'models' => [
                ['value' => 'claude-sonnet-4-6', 'label' => 'Claude Sonnet 4.6'],
                ['value' => 'claude-haiku-4-5', 'label' => 'Claude Haiku 4.5'],
            ],
        ]);
    }

    public function store(Request $request, SimulationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'rule_set_id' => ['required', 'uuid', 'exists:rule_sets,id'],
            'scenario_preset' => ['required', 'string'],
            'scenario_prompt' => ['required', 'string'],
            'model_name' => ['required', 'string', 'in:claude-sonnet-4-6,claude-haiku-4-5'],
            'auto_play' => ['boolean'],
        ]);

        $session = $service->createSession(
            ruleSetId: $validated['rule_set_id'],
            scenarioPreset: $validated['scenario_preset'],
            scenarioPrompt: $validated['scenario_prompt'],
            modelName: $validated['model_name'],
        );

        GenerateSimulation::dispatch($session, $validated['auto_play'] ?? false);

        return redirect()->route('simulation.playback', $session);
    }

    public function playback(SimulationSession $session): Response
    {
        return Inertia::render('Simulation/Playback', [
            'session' => $this->sessionData($session),
            'cloudUrl' => config('chukka.cloud_url'),
        ]);
    }

    public function play(SimulationSession $session, SimulationService $service): RedirectResponse
    {
        if ($session->status === SimulationStatus::Generated || $session->status === SimulationStatus::Paused) {
            $session->status === SimulationStatus::Paused
                ? $service->resume($session)
                : $service->startPlayback($session);
        }

        return back();
    }

    public function pause(SimulationSession $session, SimulationService $service): RedirectResponse
    {
        $service->pause($session);

        return back();
    }

    public function stop(SimulationSession $session, SimulationService $service): RedirectResponse
    {
        $service->stop($session);

        return back();
    }

    public function speed(SimulationSession $session, Request $request, SimulationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'speed' => ['required', 'numeric', 'in:0.5,1,2,5,10'],
        ]);

        $service->setSpeed($session, (float) $validated['speed']);

        return back();
    }

    public function inject(SimulationSession $session, Request $request, EventPlayerService $playerService): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_map(fn ($t) => $t->value, EventType::cases()))],
            'payload' => ['sometimes', 'array'],
        ]);

        $result = $playerService->injectEvent(
            $session,
            $validated['type'],
            $validated['payload'] ?? [],
        );

        return response()->json([
            'success' => $result->success,
            'error' => $result->error,
        ]);
    }

    public function state(SimulationSession $session): JsonResponse
    {
        return response()->json($this->sessionPollData($session->fresh()));
    }

    private function sessionData(SimulationSession $session): array
    {
        return [
            ...$this->sessionPollData($session),
            'events' => $session->generated_events ?? [],
        ];
    }

    private function sessionPollData(SimulationSession $session): array
    {
        return [
            'id' => $session->id,
            'cloud_match_id' => $session->cloud_match_id,
            'status' => $session->status->value,
            'status_label' => $session->status->label(),
            'scenario_preset' => $session->scenario_preset,
            'model_name' => $session->model_name,
            'speed_multiplier' => (float) $session->speed_multiplier,
            'current_event_index' => $session->current_event_index,
            'total_events' => $session->total_events,
            'skipped_events' => $session->skipped_events ?? [],
            'last_event_at' => $session->last_event_at?->toIso8601String(),
            'error_message' => $session->error_message,
            'created_at' => $session->created_at->toIso8601String(),
        ];
    }
}
