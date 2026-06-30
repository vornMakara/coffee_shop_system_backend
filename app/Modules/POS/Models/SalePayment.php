<?php

namespace App\Modules\POS\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'sale_id',
        'order_id',
        'payment_method_id',
        'amount',
        'amount_tendered',
        'change_amount',
    ];

    /**
     * Get the order associated with this payment.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
