# Architecture & Design Patterns

This document details the architectural decisions, design patterns, and concepts underpinning the Logistics CRM.
The goal of this architecture is to ensure the reliability of financial and logistical data, avoid high-load issues, and create code that is easy to test and maintain.

---

## 🏗️ 1. Action Pattern (Single Responsibility Actions)

Instead of using fat controllers or overloading models with business logic, the project employs a strict **Action Pattern**.

Every class in the `app/Actions` directory encapsulates **exactly one business operation**. This makes them easy to test in isolation and reuse in background queues or CLI commands.

---

## 🛡️ 2. Concurrency & Race Conditions

The system uses **Pessimistic Locking** to prevent situations where two managers might simultaneously modify the same order or assign the same truck.

---

## ⛓️ 3. Pipeline Pattern (Chain of Responsibility)

For complex modules like pricing, the `Illuminate\Pipeline\Pipeline` class is used to break the calculation into independent steps (Pipes).

---

## 🔌 4. Service Pattern & Dependency Injection

Interfaces are used for integration with external subsystems (maps, routing), allowing easy switching of providers (e.g., OSRM to Google Maps) without changing business logic.

---

## ⏱️ 5. Asynchronous Operations (Jobs & Queues)

Heavy processes (PDF generation, cloud storage uploads) are moved to the background using **Laravel Queues**, providing an instant UI response for the user.

---

## 🛡️ 6. Multi-level Validation & State Machine

Status transition control (State Machine) is implemented directly in the `OrderStatus` Enum. This ensures that invalid status manipulation is blocked even if the DB is edited directly.

---

## 🗄️ 7. Monetary Calculations (Cents Concept)

All financial data is stored in the DB as `integer` in cents (`_cents`). Calculations are performed only in integers, completely eliminating floating-point precision errors.

---

## ⚡ 8. Event-Driven Architecture (Observers & Attributes)

The CRM uses **PHP Attributes** (Laravel 11/12) for clean side-effect handling via **Observers**.

---

## 🚀 9. Extension Roadmap

Algorithm for adding a new business feature:
1. Create an Action in `app/Actions`.
2. Define business logic and service integrations.
3. Add a Feature test (`pest`).
4. Call the Action from a controller or console command.

---

## 📡 10. Real-time Infrastructure (WebSockets)

The application uses **Laravel Reverb** for high-performance, real-time UI updates (e.g., instant PDF readiness, live order status changes) via **Inertia v2 Partial Reloads** without requiring manual page refreshes.
