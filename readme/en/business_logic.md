# Business Logic & Core Concepts

This document serves as the Source of Truth for business rules, entity relationships, and access models within the Logistics CRM.

## 👥 1. Role-Based Access Control (RBAC) & Scoping

The application follows a B2B model where `Users` belong to `Companies`. Access is strictly governed via the `Permissions` Enum.

### 👑 Administrator (Carrier / Platform Owner)
- **Identification**: Represents the logistics fleet owner.
- **Capabilities**:
    - Global visibility (Policy `viewAny` returns `true`).
    - Fleet management (`Trucks`) and global settings (`Tariffs`).
    - Can assign a physical `Truck` to any `Order`.

### 🏢 Manager (Client Representative)
- **Identification**: Represents a client company (e.g., "Agro-Transit LLC").
- **Capabilities**:
    - **Visibility**: Strictly limited to their own company's data.
    - **Orders**: Can create and manage orders for their company.
    - **Documents**: Can generate and download Invoices.

### 👁️ Observer (Read-Only Client)
- **Identification**: Warehouse staff or accountants.
- **Capabilities**: Read-only access to their company's orders and dashboard metrics.

---

## 📦 2. Order Life Cycle (State Machine)

Orders follow a strict progression enforced by the `OrderStatus` Enum:
1. **New**: Created by the client; pricing calculated.
2. **Pending**: Awaiting truck assignment by the Admin.
3. **Assigned**: Truck assigned; logistics details finalized.
4. **In Transit**: Cargo is on the move (locked for editing).
5. **Delivered**: Journey completed; CMR generated.
6. **Canceled**: Terminated; reason logged in history.

---

## ⛓️ 3. Pricing Engine Workflow

Pricing is transparent and calculated using the `PricingPipeline`:
1. **Base Rate**: Based on Distance (GIS) and Vehicle Type rate.
2. **ADR Surcharge**: Applies if "Dangerous Goods" is checked.
3. **Insurance**: A percentage of the "Declared Value" of the cargo.
4. **Volume Discount**: Applied automatically if CBM exceeds a set threshold.
5. **VAT**: Calculated on the total subtotal.

---

## ⚙️ 4. Document Generation (PDF & S3)

- **Asynchronous**: Triggered via `GenerateDocumentJob` to keep the UI responsive.
- **Storage**: Uses MinIO (local) or AWS S3 (production) for persistent, scalable storage.
- **Security**: Files are served via a controller proxy to enforce RBAC (Managers cannot download other companies' CMRs).

---

## 🌍 5. Localization

- **UI**: Managed via JSON translation files (UK/EN).
- **Documents**: Invoices and CMRs are generated in the appropriate language based on the order context.
- **Formatting**: Dates and currencies are formatted according to the user's locale using the `Intl` API.
