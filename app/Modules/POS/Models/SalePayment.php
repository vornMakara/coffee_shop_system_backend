<?php

namespace App\Modules\POS\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];
    
    const UPDATED_AT = null;

    /**
     * Get the sale associated with this payment.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the payment method used for this transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
