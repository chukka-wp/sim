<?php

it('loads the setup page without JS errors', function () {
    $this->seed();

    $page = visit('/');

    $page->assertNoSmoke();
});

it('displays the setup form with all elements', function () {
    $this->seed();

    $page = visit('/');

    $page->assertSee('chukka-sim')
        ->assertSee('Match simulation tool')
        ->assertSee('Match setup')
        ->assertSee('Rule set')
        ->assertSee('Model')
        ->assertSee('Scenario')
        ->assertSee('Prompt')
        ->assertSee('Generate & Play')
        ->assertSee('Generate Only');
});

it('has rule set options in the select', function () {
    $this->seed();

    $page = visit('/');

    $page->assertSourceHas('World Aquatics 2025')
        ->assertSourceHas('FINA 2022');
});

it('has model options in the select', function () {
    $this->seed();

    $page = visit('/');

    $page->assertSourceHas('Claude Sonnet 4.5')
        ->assertSourceHas('Claude Haiku 4.5');
});

it('has scenario presets in the select', function () {
    $this->seed();

    $page = visit('/');

    $page->assertSourceHas('Routine match')
        ->assertSourceHas('Close match')
        ->assertSourceHas('Penalty shootout')
        ->assertSourceHas('Free text');
});

it('has a prompt textarea with default text', function () {
    $this->seed();

    $page = visit('/');

    // The default preset is "routine" which pre-fills the textarea
    $page->assertSourceHas('Central wins by 2');
});

it('renders correctly on mobile', function () {
    $this->seed();

    $page = visit('/')->on()->mobile();

    $page->assertNoSmoke()
        ->assertSee('chukka-sim')
        ->assertSee('Generate & Play');
});
