<?php

namespace App\Agents;

use App\Services\ScenarioPromptBuilder;
use ChukkaWp\ChukkaSpec\Models\Player;
use ChukkaWp\ChukkaSpec\Models\RuleSet;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class MatchSimulationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    private readonly ScenarioPromptBuilder $promptBuilder;

    /**
     * @param  Collection<int, Player>  $centralPlayers
     * @param  Collection<int, Player>  $eastsPlayers
     */
    public function __construct(
        private readonly RuleSet $ruleSet,
        private readonly Collection $centralPlayers,
        private readonly Collection $eastsPlayers,
    ) {
        $this->promptBuilder = app(ScenarioPromptBuilder::class);
    }

    public function instructions(): string
    {
        return $this->promptBuilder->buildSystemPrompt(
            $this->ruleSet,
            $this->centralPlayers,
            $this->eastsPlayers,
        );
    }

    public function schema(JsonSchema $schema): array
    {
        $eventSchema = $schema->object([
            'type' => $schema->string()->required(),
            'period' => $schema->integer()->required(),
            'period_clock_seconds' => $schema->integer()->required(),
            'recorded_at' => $schema->string()->required(),
            'payload' => $schema->object(),
        ]);

        return [
            'events' => $schema->array()->items($eventSchema)->required(),
        ];
    }
}
