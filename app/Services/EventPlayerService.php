<?php

namespace App\Services;

use App\Exceptions\CloudApiException;
use App\Models\SimulationSession;
use App\ValueObjects\EventPostResult;

class EventPlayerService
{
    public function __construct(
        private readonly CloudApiClient $cloudApi,
    ) {}

    public function postEvent(SimulationSession $session, array $eventData): EventPostResult
    {
        try {
            $this->cloudApi->useScorerToken($session->scorer_token);

            $response = $this->cloudApi->postEvent($session->cloud_match_id, $eventData);

            return new EventPostResult(success: true, responseData: $response);
        } catch (CloudApiException $e) {
            return new EventPostResult(success: false, error: $e->getMessage());
        }
    }

    public function injectEvent(SimulationSession $session, string $eventType, array $payload = []): EventPostResult
    {
        $state = $this->cloudApi->getMatchState($session->cloud_match_id);

        $eventData = [
            'type' => $eventType,
            'period' => $state['current_period'] ?? 1,
            'period_clock_seconds' => $state['period_clock_seconds'] ?? 0,
            'payload' => $payload,
        ];

        return $this->postEvent($session, $eventData);
    }
}
