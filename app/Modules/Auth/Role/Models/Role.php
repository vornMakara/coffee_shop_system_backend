<?php

namespace App\Modules\Auth\Role\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'is_active',
    ];

    /**
     * Get the users with this role.
     */
    public function users()
    {
        return $this->hasMany(\App\Modules\Auth\User\Models\User::class);
    }

    /**
     * Get the permissions for this role.
     */
    public function permissions()
    {
        return $this->belongsToMany(\App\Modules\Auth\Permission\Models\Permission::class, 'role_permissions', 'role_id', 'permission_id')->using(RolePermission::class);
    }
}
