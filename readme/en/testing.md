# Testing Architecture

Tests in Logistics CRM protect critical business logic, security boundaries, and financial accuracy.

## 🛠️ 1. Setup & Isolation
- **Framework**: **Pest PHP** (built on top of PHPUnit).
- **Database**: **In-Memory SQLite** for ultra-fast execution.
- **Environment**: Global `RefreshDatabase` trait ensures clean state for every test.

## 🛡️ 2. Core Testing Areas

### RBAC & Security
Ensures that a `Manager` from Company A can never see or modify orders from Company B.
```php
it('forbids manager from accessing other company orders', function () {
    $otherOrder = Order::factory()->create();
    actingAs($this->manager)
        ->get("/orders/{$otherOrder->order_number}")
        ->assertForbidden();
});
```

### Pricing Pipeline
Validates complex math, surcharges, and discounts without DB overhead.
```php
it('calculates 25% ADR surcharge correctly', function () {
    $data = new PricingData(..., isDangerous: true);
    $result = (new PricingPipeline)->calculate($data);
    expect($result->surchargeCents)->toBe(2500.0);
});
```

### Concurrency (Race Conditions)
Simulates concurrent state changes to verify that `lockForUpdate` prevents double-assignment.

## 📊 3. Coverage Strategy
- **100% Coverage**: Core Actions, Pricing Pipes, and Security Policies.
- **Mocks**: External APIs (OSRM) and S3 Storage are mocked using `Http::fake()` and `Storage::fake()`.

## 🚀 4. Running Tests
```bash
./vendor/bin/sail pest
```
For coverage reports:
```bash
./vendor/bin/sail pest --coverage
```
