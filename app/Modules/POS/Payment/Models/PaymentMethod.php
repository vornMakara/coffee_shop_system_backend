<?php

namespace App\Modules\POS\Payment\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the branch this payment method belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the sale payments made using this method.
     */
    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }
}
