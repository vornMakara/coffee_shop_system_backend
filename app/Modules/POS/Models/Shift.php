<?php

namespace App\Modules\POS\Models;

use App\Modules\Auth\Models\Branch;
use App\Modules\Auth\Models\User;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'branch_id',
        'user_id',
        'opening_cash',
        'closing_cash',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the branch this shift belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who opened this shift.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the orders created during this shift.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the cash movements during this shift.
     */
    public function cashMovements()
    {
        return $this->hasMany(ShiftCashMovement::class);
    }
}
