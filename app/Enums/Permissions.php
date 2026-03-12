<?php

declare(strict_types=1);

namespace App\Enums;

enum Permissions: string
{
    // === 1. Orders ===
    case VIEW_ALL_ORDERS = 'view all orders';
    case VIEW_COMPANY_ORDERS = 'view company orders';
    case CREATE_ORDERS = 'create orders';
    case EDIT_ORDERS = 'edit orders';
    case DELETE_ORDERS = 'delete orders';
    case CANCEL_ORDERS = 'cancel orders';

    // === 2. Order Processing & Fleet ===
    case ASSIGN_TRUCKS = 'assign trucks'; // Admin only
    case UPDATE_ORDER_STATUS = 'update order status'; // Admin only - manual status change

    // === 3. Physical Fleet (Trucks) ===
    case VIEW_TRUCKS = 'view trucks';
    case CREATE_TRUCKS = 'create trucks';
    case EDIT_TRUCKS = 'edit trucks';
    case DELETE_TRUCKS = 'delete trucks';

    // === 4. Vehicle Types ===
    case VIEW_VEHICLE_TYPES = 'view vehicle types';
    case CREATE_VEHICLE_TYPES = 'create vehicle types';
    case EDIT_VEHICLE_TYPES = 'edit vehicle types';
    case DELETE_VEHICLE_TYPES = 'delete vehicle types';

    // === 5. Tariffs & Surcharges ===
    case VIEW_TARIFFS = 'view tariffs';
    case CREATE_TARIFFS = 'create tariffs';
    case EDIT_TARIFFS = 'edit tariffs';
    case DELETE_TARIFFS = 'delete tariffs';

    // === 6. Companies / Clients ===
    case VIEW_COMPANIES = 'view companies'; // Ability to see list of all companies
    case CREATE_COMPANIES = 'create companies';
    case EDIT_COMPANIES = 'edit companies';
    case DELETE_COMPANIES = 'delete companies';

    // === 7. Users / Identity ===
    case VIEW_USERS = 'view users';
    case CREATE_USERS = 'create users';
    case EDIT_USERS = 'edit users';
    case DELETE_USERS = 'delete users';

    // === 8. Dashboards & Analytics ===
    case VIEW_GLOBAL_DASHBOARD = 'view global dashboard';
    case VIEW_COMPANY_DASHBOARD = 'view company dashboard';
}
