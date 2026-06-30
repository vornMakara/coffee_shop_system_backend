<?php

namespace App\Modules\POS\Models;

use App\Modules\Catalog\Models\Product;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name', // Snapshot at time of order
        'quantity',
        'unit_price',
        'subtotal',
        'discount_amount',
        'line_total',
        'selected_modifiers', // JSON
        'notes',
    ];

    protected $casts = [
        'selected_modifiers' => 'array',
    ];

    /**
     * Get the order this item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
