<?php

namespace App\Modules\POS\Shift\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftCashMovement extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];
    
    const UPDATED_AT = null;

    /**
     * Get the shift this cash movement belongs to.
     */
    public function shift()
    {
        return $this->belongsTo(\App\Modules\POS\Shift\Models\Shift::class);
    }
}
