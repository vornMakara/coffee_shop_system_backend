<?php

namespace App\Modules\Auth\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes;

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
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the branch this user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
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
        if ($this->role->name === 'Admin') {
            return true;
        }

        return $this->role->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Get all permissions assigned to the user via their role.
     * This is automatically appended to the User array/JSON.
     */
    public function getAllPermissionsAttribute()
    {
        if (!$this->role) {
            return [];
        }

        if ($this->role->name === 'Admin') {
            // Admin gets all permissions
            return Permission::pluck('name')->toArray();
        }

        return $this->role->permissions->pluck('name')->toArray();
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
