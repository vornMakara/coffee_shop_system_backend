<?php

namespace App\Modules\Catalog\Modifier\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the branch this group belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the options for this modifier group.
     */
    public function options()
    {
        return $this->hasMany(\App\Modules\Catalog\Modifier\Models\ModifierOption::class);
    }
}
