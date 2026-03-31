<?php

namespace App\Jobs;

use App\Models\SimulationSession;
use App\Services\SimulationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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
        $session = $service->generate($this->session);

        if ($session->status->isTerminal()) {
            return;
        }

        $session = $service->setupCloudMatch($session);

        if ($session->status->isTerminal()) {
            return;
        }

        if ($this->autoPlay) {
            $service->startPlayback($session);
        }
    }
}
