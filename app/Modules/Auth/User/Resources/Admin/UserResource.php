<?php

namespace App\Modules\Auth\User\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Role\Resources\Admin\RoleResource;
use App\Modules\Auth\Branch\Resources\Admin\BranchResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'role' => new RoleResource($this->whenLoaded('role')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('name');
            }),
            'all_permissions' => $this->all_permissions,
        ];
    }
}
