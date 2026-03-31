<?php

namespace App\Jobs;

use App\Enums\SimulationStatus;
use App\Exceptions\CloudApiException;
use App\Models\SimulationSession;
use App\Services\CloudApiClient;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PlaySimulation implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 0;

    public int $tries = 1;

    public function __construct(
        private readonly SimulationSession $session,
    ) {}

    public function uniqueId(): string
    {
        return $this->session->id;
    }

    public function handle(CloudApiClient $client): void
    {
        $session = $this->session->fresh();

        if (! $session || ! $session->isPlaying()) {
            return;
        }

        $client->useScorerToken($session->scorer_token);

        $events = $session->generated_events ?? [];
        $previousRecordedAt = null;

        for ($i = $session->current_event_index; $i < $session->total_events; $i++) {
            $session = $session->fresh();

            if (! $session->isPlaying()) {
                return;
            }

            $event = $events[$i] ?? null;

            if (! $event) {
                continue;
            }

            $currentRecordedAt = Carbon::parse($event['recorded_at']);

            if ($previousRecordedAt !== null) {
                $delayMs = (int) ($previousRecordedAt->diffInMilliseconds($currentRecordedAt) / $session->speed_multiplier);
                $this->responsiveSleep($session, $delayMs);

                $session = $session->fresh();

                if (! $session->isPlaying()) {
                    return;
                }
            }

            $previousRecordedAt = $currentRecordedAt;

            try {
                $client->postEvent($session->cloud_match_id, [
                    'type' => $event['type'],
                    'period' => $event['period'],
                    'period_clock_seconds' => $event['period_clock_seconds'],
                    'payload' => $event['payload'] ?? [],
                ]);
            } catch (CloudApiException $e) {
                if ($e->statusCode === 422) {
                    $skipped = $session->skipped_events ?? [];
                    $skipped[] = [
                        'index' => $i,
                        'type' => $event['type'],
                        'reason' => "Cloud rejected: {$e->getMessage()}",
                    ];
                    $session->update(['skipped_events' => $skipped]);

                    Log::warning("Cloud rejected event {$i}: {$e->getMessage()}", [
                        'session_id' => $session->id,
                        'event_type' => $event['type'],
                    ]);

                    continue;
                }

                Log::error("Cloud error posting event {$i}: {$e->getMessage()}", [
                    'session_id' => $session->id,
                ]);

                continue;
            }

            $session->update([
                'current_event_index' => $i + 1,
                'last_event_at' => now(),
            ]);
        }

        $session->update(['status' => SimulationStatus::Completed]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->session->update([
            'status' => SimulationStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
    }

    private function responsiveSleep(SimulationSession $session, int $delayMs): void
    {
        $remaining = $delayMs;

        while ($remaining > 0) {
            $sleepMs = min(200, $remaining);
            usleep($sleepMs * 1000);
            $remaining -= $sleepMs;

            $session = $session->fresh();

            if (! $session->isPlaying()) {
                return;
            }
        }
    }
}
