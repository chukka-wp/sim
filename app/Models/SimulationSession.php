<?php

namespace App\Models;

use App\Enums\SimulationStatus;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationSession extends Model
{
    use HasUuids;

    protected $fillable = [
        'cloud_match_id',
        'scorer_token',
        'rule_set_id',
        'scenario_preset',
        'scenario_prompt',
        'model_name',
        'status',
        'speed_multiplier',
        'generated_events',
        'current_event_index',
        'total_events',
        'skipped_events',
        'last_event_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => SimulationStatus::class,
            'speed_multiplier' => 'decimal:1',
            'generated_events' => 'array',
            'skipped_events' => 'array',
            'last_event_at' => 'datetime',
            'current_event_index' => 'integer',
            'total_events' => 'integer',
        ];
    }

    public function ruleSet(): BelongsTo
    {
        return $this->belongsTo(RuleSet::class);
    }

    public function isPlaying(): bool
    {
        return $this->status === SimulationStatus::Playing;
    }

    public function isPaused(): bool
    {
        return $this->status === SimulationStatus::Paused;
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function currentEvent(): ?array
    {
        if (! $this->generated_events || $this->current_event_index >= $this->total_events) {
            return null;
        }

        return $this->generated_events[$this->current_event_index] ?? null;
    }
}
