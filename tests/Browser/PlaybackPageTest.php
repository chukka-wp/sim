<?php

use App\Enums\SimulationStatus;
use App\Models\SimulationSession;
use ChukkaWp\ChukkaSpec\Models\RuleSet;

function createTestSession(SimulationStatus $status = SimulationStatus::Generated): SimulationSession
{
    $ruleSet = RuleSet::where('name', 'World Aquatics 2025')->firstOrFail();

    return SimulationSession::create([
        'rule_set_id' => $ruleSet->id,
        'scenario_preset' => 'routine',
        'scenario_prompt' => 'Test simulation',
        'model_name' => 'claude-sonnet-4-6',
        'status' => $status,
        'cloud_match_id' => 'test-cloud-match-id',
        'scorer_token' => 'test-scorer-token',
        'generated_events' => [
            ['type' => 'match_start', 'period' => 1, 'period_clock_seconds' => 480, 'recorded_at' => '2025-03-15T10:00:00Z', 'payload' => []],
            ['type' => 'period_start', 'period' => 1, 'period_clock_seconds' => 480, 'recorded_at' => '2025-03-15T10:00:01Z', 'payload' => []],
            ['type' => 'swim_off', 'period' => 1, 'period_clock_seconds' => 480, 'recorded_at' => '2025-03-15T10:00:03Z', 'payload' => ['winning_team_id' => 'team-1']],
            ['type' => 'goal', 'period' => 1, 'period_clock_seconds' => 420, 'recorded_at' => '2025-03-15T10:01:00Z', 'payload' => ['team_id' => 'team-1', 'cap_number' => 7, 'home_score_after' => 1, 'away_score_after' => 0]],
            ['type' => 'period_end', 'period' => 1, 'period_clock_seconds' => 0, 'recorded_at' => '2025-03-15T10:08:00Z', 'payload' => []],
        ],
        'total_events' => 5,
        'current_event_index' => 0,
        'skipped_events' => [],
    ]);
}

it('loads the playback page without JS errors', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->assertNoSmoke();
});

it('shows the score display and event log', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('chukka-sim')
        ->assertSee('Central')
        ->assertSee('Easts')
        ->assertSee('Event Log')
        ->assertSee('0 / 5 events');
});

it('shows the play button when session is generated', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Generated);

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Play');
});

it('shows speed controls', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Speed:')
        ->assertSee('0.5x')
        ->assertSee('1x')
        ->assertSee('2x')
        ->assertSee('5x')
        ->assertSee('10x');
});

it('shows event log entries', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Match Start')
        ->assertSee('Period Start')
        ->assertSee('Swim Off')
        ->assertSee('Goal')
        ->assertSee('Period End');
});

it('shows the new simulation link', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->assertSeeLink('New simulation');
});

it('navigates back to setup page', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}");

    $page->click('New simulation')
        ->assertPathIs('/')
        ->assertSee('Match setup');
});

it('shows generating spinner for pending sessions', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Pending);

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Generating events...');
});

it('shows error state for failed sessions', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Failed);
    $session->update(['error_message' => 'LLM returned invalid JSON']);

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Simulation failed')
        ->assertSee('LLM returned invalid JSON');
});

it('shows completed status for finished sessions', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Completed);
    $session->update(['current_event_index' => 5]);

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Completed')
        ->assertSee('5 / 5 events')
        ->assertSee('100%');
});

it('shows inject panel for non-terminal sessions', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Generated);

    $page = visit("/simulation/{$session->id}");

    $page->assertSee('Inject Event')
        ->assertSee('Simultaneous Exclusion')
        ->assertSee('Goalkeeper Substitution');
});

it('hides inject panel for completed sessions', function () {
    $this->seed();
    $session = createTestSession(SimulationStatus::Completed);

    $page = visit("/simulation/{$session->id}");

    $page->assertDontSee('Inject Event');
});

it('renders correctly on mobile', function () {
    $this->seed();
    $session = createTestSession();

    $page = visit("/simulation/{$session->id}")->on()->mobile();

    $page->assertNoSmoke()
        ->assertSee('chukka-sim')
        ->assertSee('Event Log');
});
