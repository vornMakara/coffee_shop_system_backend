<?php

namespace App\Modules\POS\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftCashMovement extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'shift_id',
        'type', // 'in', 'out'
        'amount',
        'note',
    ];

    /**
     * Get the shift this cash movement belongs to.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
