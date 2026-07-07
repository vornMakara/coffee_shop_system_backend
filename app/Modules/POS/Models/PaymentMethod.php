<?php

namespace App\Modules\POS\Models;

use App\Modules\Auth\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];

    /**
     * Get the branch this payment method belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the sale payments made using this method.
     */
    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }
}
