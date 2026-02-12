<?php

test('valid auth path returns login form', function () {
    $response = $this->get('/pop/login');
    $response->assertStatus(200);
    $response->assertSee('login', false); // Check for login form or text
});


test('can fetch clients from database', function () {
    $clients = \App\Models\Client::all();
    expect($clients)->not->toBeEmpty();
});

test('database returns at least one client', function () {
    // Create a test client if none exists
    \App\Models\Client::firstOrCreate(['name' => 'Test Client']);
    
    $clients = \App\Models\Client::all();
    expect($clients->count())->toBeGreaterThanOrEqual(1);
});
