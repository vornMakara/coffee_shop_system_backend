<?php

namespace App\Modules\Auth\Role\Services\Admin;

use App\Modules\Auth\Role\Models\Role;
use App\Modules\Auth\Permission\Models\Permission;

class RoleService
{
    public function getAllRoles()
    {
        return Role::with('permissions')->get();
    }

    public function createRole(array $data)
    {
        $role = Role::create([
            'name' => $data['name'],
            'display_name' => ucfirst($data['name']),
            'is_active' => true
        ]);

        if (!empty($data['permissions'])) {
            $permissionIds = Permission::whereIn('name', $data['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        return $role->load('permissions');
    }

    public function updateRole(Role $role, array $data)
    {
        $role->update([
            'name' => $data['name'],
            'display_name' => ucfirst($data['name']),
        ]);
        return $role;
    }

    public function updateRolePermissions(Role $role, array $permissions)
    {
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
        $role->permissions()->sync($permissionIds);
        return $role->load('permissions');
    }
}
