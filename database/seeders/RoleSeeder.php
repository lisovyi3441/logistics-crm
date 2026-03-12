<?php

namespace Database\Seeders;

use App\Enums\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions from Enum
        foreach (Permissions::cases() as $permission) {
            Permission::firstOrCreate(['name' => $permission->value]);
        }

        // Create and assign roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Manager Role (Client Manager)
        $managerRole = Role::firstOrCreate(['name' => 'manager']);
        $managerRole->syncPermissions([
            // Orders
            Permissions::VIEW_COMPANY_ORDERS->value,
            Permissions::CREATE_ORDERS->value,
            Permissions::EDIT_ORDERS->value,
            Permissions::CANCEL_ORDERS->value,
            // Types & Dashboards
            Permissions::VIEW_VEHICLE_TYPES->value,
            Permissions::VIEW_COMPANY_DASHBOARD->value,
        ]);

        // Observer Role (Observer - Read-only)
        $observerRole = Role::firstOrCreate(['name' => 'observer']);
        $observerRole->syncPermissions([
            Permissions::VIEW_COMPANY_ORDERS->value,
            Permissions::VIEW_VEHICLE_TYPES->value,
            Permissions::VIEW_COMPANY_DASHBOARD->value,
        ]);
    }
}
