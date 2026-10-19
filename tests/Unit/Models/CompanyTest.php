<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Order;
use App\Models\User;

it('has users relationship', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    expect($company->users)->toHaveCount(1)
        ->and($company->users->first()->id)->toBe($user->id);
});

it('has orders relationship', function () {
    $company = Company::factory()->create();
    $order = Order::factory()->create(['company_id' => $company->id]);

    expect($company->orders)->toHaveCount(1)
        ->and($company->orders->first()->id)->toBe($order->id);
});

it('can be deleted only when it has no users and no orders', function () {
    $company = Company::factory()->create();

    expect($company->canBeDeleted())->toBeTrue();

    $user = User::factory()->create(['company_id' => $company->id]);
    expect($company->fresh()->canBeDeleted())->toBeFalse();

    $user->delete();
    expect($company->fresh()->canBeDeleted())->toBeTrue();

    Order::factory()->create(['company_id' => $company->id]);
    expect($company->fresh()->canBeDeleted())->toBeFalse();
});
