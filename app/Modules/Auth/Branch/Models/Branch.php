<?php

namespace App\Modules\Auth\Branch\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the users belonging to this branch.
     */
    public function users()
    {
        return $this->hasMany(\App\Modules\Auth\User\Models\User::class);
    }
}
