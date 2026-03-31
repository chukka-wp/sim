<?php

namespace App\Services;

use App\ValueObjects\ValidationResult;
use ChukkaWp\ChukkaSpec\Enums\EventType;
use ChukkaWp\ChukkaSpec\Exceptions\InvalidPayloadException;
use ChukkaWp\ChukkaSpec\Payloads\PayloadFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EventValidatorService
{
    /**
     * @param  array<int, array>  $rawEvents
     * @param  array<string>  $homePlayerIds
     * @param  array<string>  $awayPlayerIds
     */
    public function validate(
        array $rawEvents,
        array $homePlayerIds,
        array $awayPlayerIds,
        string $homeTeamId,
        string $awayTeamId,
    ): ValidationResult {
        $validEvents = [];
        $skippedEvents = [];
        $allPlayerIds = array_merge($homePlayerIds, $awayPlayerIds);
        $validTeamIds = [$homeTeamId, $awayTeamId];

        foreach ($rawEvents as $index => $event) {
            $reason = $this->validateEvent($event, $allPlayerIds, $validTeamIds);

            if ($reason !== null) {
                $skippedEvents[] = [
                    'index' => $index,
                    'type' => $event['type'] ?? 'unknown',
                    'reason' => $reason,
                ];

                Log::warning("Skipping event {$index}: {$reason}", ['event' => $event]);

                continue;
            }

            $validEvents[] = $event;
        }

        return new ValidationResult($validEvents, $skippedEvents);
    }

    private function validateEvent(array $event, array $allPlayerIds, array $validTeamIds): ?string
    {
        if (! isset($event['type']) || ! is_string($event['type'])) {
            return 'Missing or invalid event type';
        }

        $eventType = EventType::tryFrom($event['type']);

        if ($eventType === null) {
            return "Unknown event type: {$event['type']}";
        }

        if (! isset($event['period']) || ! is_numeric($event['period'])) {
            return 'Missing or invalid period';
        }

        if (! isset($event['period_clock_seconds']) || ! is_numeric($event['period_clock_seconds'])) {
            return 'Missing or invalid period_clock_seconds';
        }

        if (! isset($event['recorded_at'])) {
            return 'Missing recorded_at timestamp';
        }

        try {
            Carbon::parse($event['recorded_at']);
        } catch (\Exception) {
            return "Invalid recorded_at timestamp: {$event['recorded_at']}";
        }

        $payload = $event['payload'] ?? [];

        if ($eventType->hasPayload()) {
            if (empty($payload)) {
                return "Missing required payload for event type: {$event['type']}";
            }

            try {
                PayloadFactory::make($eventType, $payload);
            } catch (InvalidPayloadException $e) {
                return "Payload validation failed: {$e->getMessage()}";
            }
        }

        if (! empty($payload)) {
            $teamIdFields = ['team_id', 'offending_team_id', 'winning_team_id', 'shooting_team_id', 'free_throw_team_id'];

            foreach ($teamIdFields as $field) {
                if (isset($payload[$field]) && ! in_array($payload[$field], $validTeamIds)) {
                    return "Invalid {$field}: {$payload[$field]}";
                }
            }

            $playerIdFields = ['player_id', 'offending_player_id', 'shooter_player_id', 'home_player_id', 'away_player_id'];

            foreach ($playerIdFields as $field) {
                if (isset($payload[$field]) && ! in_array($payload[$field], $allPlayerIds)) {
                    return "Invalid {$field}: {$payload[$field]}";
                }
            }
        }

        return null;
    }
}
