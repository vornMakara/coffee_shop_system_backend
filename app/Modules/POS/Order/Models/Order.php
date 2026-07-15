<?php

namespace App\Modules\POS\Order\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\User\Models\User;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the branch where the order was placed.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the shift during which the order was placed.
     */
    public function shift()
    {
        return $this->belongsTo(\App\Modules\POS\Shift\Models\Shift::class);
    }

    /**
     * Get the user (cashier) who processed the order.
     */
    public function user()
    {
        return $this->belongsTo(\App\Modules\Auth\User\Models\User::class);
    }

    /**
     * Get the items in this order.
     */
    public function items()
    {
        return $this->hasMany(\App\Modules\POS\Order\Models\OrderItem::class);
    }

    /**
     * Get the sale associated with this order.
     */
    public function sale()
    {
        return $this->hasOne(\App\Modules\POS\Payment\Models\Sale::class);
    }
}
