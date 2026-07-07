<?php

namespace App\Modules\POS\Models;

use App\Modules\Auth\Models\Branch;
use App\Modules\Auth\Models\User;
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
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the shift during which the order was placed.
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user (cashier) who processed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the sale associated with this order.
     */
    public function sale()
    {
        return $this->hasOne(Sale::class);
    }
}
