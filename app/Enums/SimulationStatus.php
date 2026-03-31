<?php

namespace App\Enums;

enum SimulationStatus: string
{
    case Pending = 'pending';
    case Generating = 'generating';
    case Generated = 'generated';
    case Playing = 'playing';
    case Paused = 'paused';
    case Stopped = 'stopped';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Generating => 'Generating events...',
            self::Generated => 'Ready to play',
            self::Playing => 'Playing',
            self::Paused => 'Paused',
            self::Stopped => 'Stopped',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Stopped, self::Completed, self::Failed]);
    }
}
