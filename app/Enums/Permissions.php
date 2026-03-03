<?php

declare(strict_types=1);

namespace App\Enums;

enum Permissions: string
{
    // === 1. Orders (Замовлення) ===
    case VIEW_ALL_ORDERS = 'view all orders';
    case VIEW_COMPANY_ORDERS = 'view company orders';
    case CREATE_ORDERS = 'create orders';
    case EDIT_ORDERS = 'edit orders';
    case DELETE_ORDERS = 'delete orders';
    case CANCEL_ORDERS = 'cancel orders';

    // === 2. Order Processing & Fleet (Обробка та Автопарк) ===
    case ASSIGN_TRUCKS = 'assign trucks'; // Тільки Адмін
    case UPDATE_ORDER_STATUS = 'update order status'; // Тільки Адмін - ручна зміна статусу
    
    // === 3. Physical Fleet (Фізичний Автопарк - Trucks) ===
    case VIEW_TRUCKS = 'view trucks';
    case CREATE_TRUCKS = 'create trucks';
    case EDIT_TRUCKS = 'edit trucks';
    case DELETE_TRUCKS = 'delete trucks';

    // === 4. Vehicle Types (Типи Транспорту - Абстракція) ===
    case VIEW_VEHICLE_TYPES = 'view vehicle types';
    case CREATE_VEHICLE_TYPES = 'create vehicle types';
    case EDIT_VEHICLE_TYPES = 'edit vehicle types';
    case DELETE_VEHICLE_TYPES = 'delete vehicle types';

    // === 5. Tariffs (Тарифи та Націнки) ===
    case VIEW_TARIFFS = 'view tariffs';
    case CREATE_TARIFFS = 'create tariffs';
    case EDIT_TARIFFS = 'edit tariffs';
    case DELETE_TARIFFS = 'delete tariffs';

    // === 6. Companies (Компанії/Клієнти) ===
    case VIEW_COMPANIES = 'view companies'; // Можливість бачити список всіх компаній
    case CREATE_COMPANIES = 'create companies';
    case EDIT_COMPANIES = 'edit companies';
    case DELETE_COMPANIES = 'delete companies';

    // === 7. Users / Identity (Користувачі) ===
    case VIEW_USERS = 'view users';
    case CREATE_USERS = 'create users';
    case EDIT_USERS = 'edit users';
    case DELETE_USERS = 'delete users';

    // === 8. Dashboards & Analytics (Аналітика) ===
    case VIEW_GLOBAL_DASHBOARD = 'view global dashboard';
    case VIEW_COMPANY_DASHBOARD = 'view company dashboard';
}
