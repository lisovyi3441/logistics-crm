# Database Schema

The project uses a relational database (MySQL 8.4). This document describes the key tables and their relationships.

## Entity Relationship Overview

- **`companies`** has many **`users`** and **`orders`**.
- **`vehicle_types`** (e.g., "20t Truck", "Reefer") defines rates and requirements.
- **`trucks`** belong to a `vehicle_type` and are owned by the platform.
- **`orders`** belong to a `company` and a `user`. They have one requested `vehicle_type` and one assigned `truck`.
- **`order_items`** belong to an `order` (Cascade Delete).
- **`order_status_histories`** provide an audit trail for every status change.
- **`order_documents`** store paths to files in S3/MinIO.

---

## 🗄️ Table Details

### `orders` (Main Entity)
- `order_number`: Unique human-readable ID (e.g., ORD-ABC123).
- **Financials (Cents)**: `total_price_cents`, `base_price_cents`, `tax_cents`, etc.
- **Routing**: `pickup_address`, `delivery_address`, `pickup_lat/lng`, `delivery_lat/lng`, `distance_km`, `transit_time_minutes`.

### `order_items`
- Stores weight, volume (CBM), dimensions, and "is_dangerous" flag.
- `declared_value_cents`: Basis for insurance calculation.

---

## ⚡ Optimizations
1. **Foreign Keys**: Strict `constrained()` usage for referential integrity.
2. **Indexes**: Added to `status`, `order_number`, and foreign keys for high performance.
3. **Pessimistic Locking**: `lockForUpdate()` used for atomic operations on `orders` and `trucks`.
