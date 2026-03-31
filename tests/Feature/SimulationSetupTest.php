<?php

it('loads the setup page with rule sets and presets', function () {
    $this->seed();

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Simulation/Setup')
        ->has('ruleSets')
        ->has('presets')
        ->has('models')
    );
});

it('has bundled rule sets available', function () {
    $this->seed();

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->has('ruleSets', 5)
    );
});
