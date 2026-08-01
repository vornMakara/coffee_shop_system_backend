<?php

namespace App\Modules\Auth\User\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use App\Modules\Auth\Permission\Models\Permission;
use App\Modules\Auth\Role\Models\Role;
use App\Modules\Auth\Branch\Models\Branch;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $fillable = [
        'branch_id',
        'role_id',
        'username',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $appends = [
        'all_permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(\App\Modules\Auth\Role\Models\Role::class);
    }

    /**
     * Get the branch this user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the direct permissions associated with the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(\App\Modules\Auth\Permission\Models\Permission::class, 'user_permissions');
    }

    /**
     * Check if the user has a specific permission via their role.
     */
    public function hasPermission($permissionName)
    {
        if (!$this->role) {
            return false;
        }

        // Admin has all permissions automatically
        if (strtolower($this->role->name) === 'admin') {
            return true;
        }

        if ($this->role->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        return $this->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Get all permissions assigned to the user via their role.
     * This is automatically appended to the User array/JSON.
     */
    public function getAllPermissionsAttribute()
    {
        $rolePermissions = collect();
        
        if ($this->role) {
            if (strtolower($this->role->name) === 'admin') {
                return Permission::pluck('name')->toArray();
            }
            $rolePermissions = collect($this->role->permissions->pluck('name'));
        }

        $directPermissions = collect();
        if ($this->relationLoaded('permissions')) {
            $directPermissions = collect($this->permissions->pluck('name'));
        } else {
            $directPermissions = collect($this->permissions()->pluck('name'));
        }

        return $rolePermissions->merge($directPermissions)->unique()->values()->toArray();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
