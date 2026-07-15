<?php

namespace App\Modules\Auth\Permission\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group_name'
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(\App\Modules\Auth\Role\Models\Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
