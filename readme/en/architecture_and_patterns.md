# Architecture & Design Patterns

This document details the architectural decisions, design patterns, and concepts underlying Logistics CRM. The goal is to ensure data integrity, handle high concurrency, and create a maintainable, testable codebase.

---

## 🏗️ 1. Action Pattern (Single Responsibility Actions)

To avoid "Fat Controllers" or bloated models, the project employs a strict **Action Pattern**. Each class in the `app/Actions` directory encapsulates **exactly one business operation**.

### Example: `CreateOrderAction`
Responsible solely for order creation, coordinating several services:
1. Fetching distance/time via `RoutingServiceInterface` (**Outside of DB Transaction** to prevent connection pool exhaustion).
2. Calculating price through the `PricingPipeline`.
3. Persisting the `Order`, `OrderItems`, and `OrderStatusHistory` within a single **Database Transaction**.

---

## 🛡️ 2. Concurrency & Race Condition Protection

In logistics, multiple managers might attempt to assign the same truck or update the same order simultaneously. We use **Pessimistic Locking** to handle this.

### Implementation:
Critical Actions use the database-level `lockForUpdate()` method (InnoDB Row-Level Lock) to ensure atomicity.

**Example (from `AssignTruckAction.php`):**
```php
DB::transaction(function () use ($order, $truckId) {
    // 1. Lock the order to prevent status changes during assignment
    Order::where('id', $order->id)->lockForUpdate()->value('id');
    $order->refresh();

    // 2. Lock the truck to prevent it from being assigned to another order simultaneously
    $truck = Truck::where('id', $truckId)->lockForUpdate()->firstOrFail();

    // Safe validation and update logic...
});
```

---

## ⛓️ 3. Pipeline Pattern (Chain of Responsibility)

For complex modules like pricing, monolithic functions become unmaintainable. CRM uses `Illuminate\Pipeline\Pipeline` to break calculations into independent **Pipes**.

### `PricingPipeline` Workflow:
1. **`CalculateBasePrice`**: Distance × Vehicle Rate.
2. **`ApplyDangerousGoodsSurcharge`**: ADR % surcharge.
3. **`ApplyInsuranceFee`**: % of declared cargo value.
4. **`ApplyVolumeDiscount`**: Automated discounts based on CBM volume.
5. **`ApplyTax`**: Calculates VAT on the final subtotal.

---

## 🔌 4. Service Pattern & Dependency Inversion

We use interfaces for external integrations to ensure flexibility and testability.

### `RoutingServiceInterface`
Allows switching between OSRM, Google Maps, or a Haversine fallback without changing business logic. It also enables easy mocking in tests.

**Performance Note:** To ensure OSRM API latency doesn't block the database, all routing calls are performed **before** opening a database transaction. Results are cached in Redis for 24 hours (only successful API responses are cached to prevent "cache poisoning" from temporary failures).

---

## ⏱️ 5. Async Operations (Jobs & Queues)

UI Philosophy: No user should wait more than 200ms. Heavy tasks are delegated to **Laravel Queues**.

**Example: `GenerateDocumentJob`**
PDF generation (CMR/Invoices) and S3 uploads are handled in the background by `frankenphp-worker`.

---

## 🛡️ 6. State Machine via Enums

We enforce strict status transition rules directly within the `OrderStatus` Enum. This prevents invalid state changes even if the database is manipulated directly via code.

---

## 🗄️ 7. Financial Precision (Cents Concept)

All financial data is stored as `integers` with a `_cents` suffix (e.g., `total_price_cents`). This completely eliminates floating-point precision errors. Division by 100 occurs **only at the presentation layer**.

---

## 🚀 8. Extension Roadmap

To add a new feature while maintaining architectural integrity:
1. **Database**: Migration with proper FK constraints and indexes.
2. **Model**: Define casts (Enums/Cents) and typed relationships.
3. **Action**: Encapsulate the business logic (use transactions if needed).
4. **Form Request**: Define validation rules.
5. **Controller**: Thin controller calling the Action and returning `Inertia::render`.
6. **Vue Page**: UI implementation using existing components.
7. **Test**: Pest Feature test verifying logic and RBAC constraints.

---

## ⚡ 9. Event-driven Architecture (Observers & Attributes)

Logistics CRM leverages **PHP Attributes** (introduced in PHP 8.0/8.2 and deeply integrated into Laravel 11/12) to handle side effects cleanly via **Observers**.

### Modern Observer Registration:
Instead of polluting `AppServiceProvider`, we use the `#[ObservedBy]` attribute directly on models:

```php
#[ObservedBy(OrderObserver::class)]
class Order extends Model { ... }
```

### Automatic Audit Trail:
The `OrderObserver` automatically listens for changes in the `status` field. When a change occurs, it generates a record in the `OrderStatusHistory` table. 

**Intelligent Context:** The system automatically detects the initiator of the change:
- If a user is logged in, the history record includes their ID and a comment: `"Updated manually."`.
- If the change is triggered by a background process (Job/CLI), the record is marked as `"Updated by the system."`.

---

## 📡 10. Real-time Infrastructure (WebSockets)

The application uses **Laravel Reverb** for high-performance, real-time communication. This eliminates the need for manual page refreshes during long-running or collaborative tasks.

### Key Real-time Features:
- **Instant PDF Feedback:** When a background PDF generation job finishes, the UI is notified via the `DocumentGenerated` event, and the documents list performs a partial reload.
- **Order Synchronization:** Any status change or truck assignment triggers an `OrderUpdated` event, syncing the state across all active managers of the same company.
- **Optimized UI Updates:** Uses **Inertia v2 Partial Reloads** (`router.reload({ only: [...] })`), ensuring that only the necessary JSON data is fetched, preserving the frontend state and minimizing server load.

### Deployment Architecture:
In production, Reverb runs as a dedicated service within the Docker cluster, handling thousands of concurrent connections without impacting the main Octane application performance.
