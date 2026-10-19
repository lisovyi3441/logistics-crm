<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Order;
use App\Models\User;

it('belongs to a company', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    expect($user->company)->toBeInstanceOf(Company::class)
        ->and($user->company->id)->toBe($company->id);
});

it('has orders relationship', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    expect($user->orders)->toHaveCount(1)
        ->and($user->orders->first()->id)->toBe($order->id);
});

it('can be deleted only when it has no orders', function () {
    $user = User::factory()->create();

    expect($user->canBeDeleted())->toBeTrue();

    $order = Order::factory()->create(['user_id' => $user->id]);
    expect($user->fresh()->canBeDeleted())->toBeFalse();

    $order->delete();
    expect($user->fresh()->canBeDeleted())->toBeTrue();
});
