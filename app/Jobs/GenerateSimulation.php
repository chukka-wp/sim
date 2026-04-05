<?php

namespace App\Jobs;

use App\Models\SimulationSession;
use App\Services\SimulationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateSimulation implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 1;

    public function __construct(
        private readonly SimulationSession $session,
        private readonly bool $autoPlay = false,
    ) {}

    public function uniqueId(): string
    {
        return $this->session->id;
    }

    public function handle(SimulationService $service): void
    {
        Log::info('[sim] Job started', [
            'session_id' => $this->session->id,
            'auto_play' => $this->autoPlay,
        ]);

        $session = $service->generate($this->session);

        if ($session->status->isTerminal()) {
            Log::warning('[sim] Generation ended in terminal state', [
                'session_id' => $session->id,
                'status' => $session->status->value,
                'error' => $session->error_message,
            ]);

            return;
        }

        Log::info('[sim] Generation complete, setting up cloud match', [
            'session_id' => $session->id,
            'total_events' => $session->total_events,
        ]);

        $session = $service->setupCloudMatch($session);

        if ($session->status->isTerminal()) {
            Log::warning('[sim] Cloud setup ended in terminal state', [
                'session_id' => $session->id,
                'status' => $session->status->value,
                'error' => $session->error_message,
            ]);

            return;
        }

        Log::info('[sim] Cloud match ready', [
            'session_id' => $session->id,
            'cloud_match_id' => $session->cloud_match_id,
        ]);

        if ($this->autoPlay) {
            $service->startPlayback($session);
            Log::info('[sim] Auto-play started', ['session_id' => $session->id]);
        }
    }
}
