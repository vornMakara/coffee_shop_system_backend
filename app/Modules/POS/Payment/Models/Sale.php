<?php

namespace App\Modules\POS\Payment\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\Auth\User\Models\User;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'sale_date' => 'date',
        'business_date' => 'date',
    ];

    /**
     * Get the order associated with this sale.
     */
    public function order()
    {
        return $this->belongsTo(\App\Modules\POS\Order\Models\Order::class);
    }

    /**
     * Get the payments made for this sale.
     */
    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Get the branch where the sale occurred.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the shift during which the sale occurred.
     */
    public function shift()
    {
        return $this->belongsTo(\App\Modules\POS\Shift\Models\Shift::class);
    }

    /**
     * Get the user (cashier) who processed the sale.
     */
    public function user()
    {
        return $this->belongsTo(\App\Modules\Auth\User\Models\User::class);
    }

    /**
     * Get the customer who made the purchase.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Modules\POS\Customer\Models\Customer::class);
    }
}
