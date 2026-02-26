<?php

use Database\Seeders\RoleSeeder;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $this->withoutExceptionHandling();
    $this->seed(RoleSeeder::class);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $errors = session('errors');
    if ($errors) {
        dump($errors->getBag('default')->getMessages());
    }

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
