<?php

namespace App\Modules\Catalog\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierOption extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the group this option belongs to.
     */
    public function modifierGroup()
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
