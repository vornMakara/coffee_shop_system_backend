<?php

namespace App\Modules\POS\Shift\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\User\Models\User;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the branch this shift belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the user who opened this shift.
     */
    public function user()
    {
        return $this->belongsTo(\App\Modules\Auth\User\Models\User::class);
    }

    /**
     * Get the orders created during this shift.
     */
    public function orders()
    {
        return $this->hasMany(\App\Modules\POS\Order\Models\Order::class);
    }

    /**
     * Get the cash movements during this shift.
     */
    public function cashMovements()
    {
        return $this->hasMany(ShiftCashMovement::class);
    }
}
