<?php

namespace App\Modules\Auth\User\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Support\Traits\HasUuid;

class UserPermission extends Pivot
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'user_permissions';

    protected $fillable = [
        'id',
        'user_id',
        'permission_id'
    ];
}
