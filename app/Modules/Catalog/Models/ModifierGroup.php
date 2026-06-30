<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Auth\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'min_select',
        'max_select',
        'is_active',
    ];

    /**
     * Get the branch this group belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the options for this modifier group.
     */
    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }
}
