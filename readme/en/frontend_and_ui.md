# Frontend & UI/UX Architecture

The Logistics CRM frontend is a reactive SPA built with **Vue 3** and **Inertia.js v2**, providing a seamless desktop-like experience.

---

## 🏗️ 1. UI Stack
- **Framework**: Vue 3 (Composition API).
- **Styling**: Tailwind CSS v4.
- **Components**: Radix Vue (`reka-ui`) for accessible primitives.
- **Icons**: Lucide Vue Next.
- **Maps**: Leaflet.js for route visualization.

---

## ⚡ 2. Inertia v2 Patterns
- **Deferred Props**: Heavy statistics load asynchronously with skeletons, keeping the initial page load instant.
- **Wayfinder**: Strongly typed routing in TypeScript. No more hardcoded URLs.
- **Form Handling**: Native `useForm` hook with automatic server-side validation error mapping.

---

## 🗺️ 3. GIS & Maps
- **Route Visualization**: Renders paths using polylines from the OSRM backend.
- **Interactive Search**: Address autocomplete using OpenStreetMap (OSM) providers.

---

## 🎨 4. Design System
- **Dark Mode**: Native support via Tailwind v4 CSS variables.
- **Modular Components**: Custom UI kit built on top of Radix for consistent UX.
- **Responsive**: Mobile-first design for all tables and forms.
