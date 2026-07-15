<?php

namespace App\Modules\Auth\User\Services\Admin;

use App\Modules\Auth\User\Models\User;
use App\Modules\Auth\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;

class UserService
{
    public function getPaginated(int $perPage = 15)
    {
        return User::with(['role', 'branch', 'permissions'])->paginate($perPage);
    }

    public function createUser(array $data)
    {
        $userData = Arr::except($data, ['permissions']);
        $userData['password'] = Hash::make($userData['password']);
        
        $user = User::create($userData);

        if (!empty($data['permissions'])) {
            $permissionIds = Permission::whereIn('name', $data['permissions'])->pluck('id');
            $user->permissions()->sync($permissionIds);
        }

        return $user->load(['role', 'branch', 'permissions']);
    }

    public function updateUser(User $user, array $data)
    {
        $updateData = Arr::except($data, ['permissions']);

        if (isset($updateData['password'])) {
            $updateData['password'] = Hash::make($updateData['password']);
        }

        $user->update($updateData);

        if (isset($data['permissions'])) {
            $permissionIds = Permission::whereIn('name', $data['permissions'])->pluck('id');
            $user->permissions()->sync($permissionIds);
        }

        return $user->load(['role', 'branch', 'permissions']);
    }

    public function deleteUser(User $user)
    {
        return $user->delete();
    }
}
