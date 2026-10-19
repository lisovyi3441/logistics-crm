<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;

it('bypasses email verification for unverified user and fires event', function () {
    Event::fake();

    $user = User::factory()->unverified()->create();

    $response = actingAs($user)->post('/email/verify/demo-bypass');

    $response->assertRedirect(config('fortify.home'))
        ->assertSessionHas('status', 'Email successfully verified automatically for demo mode.');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class, fn ($e) => $e->user->id === $user->id);
});

it('redirects already verified user without re-verifying', function () {
    Event::fake();

    $user = User::factory()->create();

    $response = actingAs($user)->post('/email/verify/demo-bypass');

    $response->assertRedirect(config('fortify.home'));

    Event::assertNotDispatched(Verified::class);
});
