<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\Auth\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 0. Sync Permissions first
        \Illuminate\Support\Facades\Artisan::call('rbac:sync');

        // 1. Create Default Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['display_name' => 'System Administrator', 'description' => 'Full access to all features.']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager'], ['display_name' => 'Shop Manager', 'description' => 'Can manage staff, reports, and overrides.']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier'], ['display_name' => 'Cashier', 'description' => 'Can operate the POS and take payments.']);
        $kitchenRole = Role::firstOrCreate(['name' => 'Kitchen'], ['display_name' => 'Kitchen Staff', 'description' => 'Can view and update KDS tickets.']);
        $waiterRole = Role::firstOrCreate(['name' => 'Waiter'], ['display_name' => 'Waiter', 'description' => 'Can create orders but not take payments.']);

        // 2. Fetch all permissions
        $allPermissions = Permission::all();
        $posAccess = $allPermissions->where('name', 'pos.access')->first();
        $posPayments = $allPermissions->where('name', 'pos.payments')->first();
        $posVoid = $allPermissions->where('name', 'pos.void')->first();
        $posRemoveItem = $allPermissions->where('name', 'pos.remove_item')->first();
        $posDiscount = $allPermissions->where('name', 'pos.discount')->first();
        $posShifts = $allPermissions->where('name', 'pos.shifts')->first();
        $kdsAccess = $allPermissions->where('name', 'kds.access')->first();
        $kdsUpdate = $allPermissions->where('name', 'kds.update')->first();

        // 3. Assign to Admin (Everything)
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // 4. Assign to Manager
        $managerRole->permissions()->sync($allPermissions->whereNotIn('name', ['admin.roles'])->pluck('id'));

        // 5. Assign to Cashier
        $cashierRole->permissions()->sync([
            $posAccess->id,
            $posPayments->id,
            $posShifts->id,
            $posRemoveItem->id // Cashiers can remove items, but maybe not void entire orders
        ]);

        // 6. Assign to Waiter
        $waiterRole->permissions()->sync([
            $posAccess->id
        ]);

        // 7. Assign to Kitchen
        $kitchenRole->permissions()->sync([
            $kdsAccess->id,
            $kdsUpdate->id
        ]);
    }
}
