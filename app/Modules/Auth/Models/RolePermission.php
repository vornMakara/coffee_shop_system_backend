<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Support\Traits\HasUuid;

class RolePermission extends Pivot
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'role_permissions';

    protected $fillable = [
        'id',
        'role_id',
        'permission_id'
    ];
}
