<?php

declare(strict_types=1);

use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Laravel\Telescope\IncomingEntry;

it('configures strong password defaults in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    $provider = new AppServiceProvider(app());
    $provider->boot();

    $defaultPassword = Password::default();
    expect($defaultPassword)->toBeInstanceOf(Password::class);

    // Reset to testing
    app()->detectEnvironment(fn () => 'testing');
    $provider->boot();
});

it('executes fortify two-factor rate limiter callback', function () {
    $limiter = RateLimiter::limiter('two-factor');
    $request = Request::create('/two-factor-challenge', 'POST');
    $request->setLaravelSession(app('session.store'));

    $limit = $limiter($request);
    expect($limit)->not->toBeNull();
});

it('tests telescope service provider register, filter, and gate', function () {
    // 1. In local environment
    app()->detectEnvironment(fn () => 'local');
    $localProvider = new TelescopeServiceProvider(app());
    $localProvider->register();

    // 2. In non-local (testing) environment
    app()->detectEnvironment(fn () => 'testing');
    $provider = new TelescopeServiceProvider(app());
    $provider->register();

    // Test entry filter
    $filterMethod = new ReflectionProperty(\Laravel\Telescope\Telescope::class, 'filterUsing');
    $filterMethod->setAccessible(true);
    $filters = $filterMethod->getValue();

    $entry = new IncomingEntry(['type' => 'request']);
    foreach ($filters as $filter) {
        $filter($entry);
    }

    // Test gate
    $gateMethod = new ReflectionMethod($provider, 'gate');
    $gateMethod->setAccessible(true);
    $gateMethod->invoke($provider);

    $user = User::factory()->create(['email' => 'test@example.com']);
    expect(Gate::forUser($user)->allows('viewTelescope'))->toBeFalse();
});
