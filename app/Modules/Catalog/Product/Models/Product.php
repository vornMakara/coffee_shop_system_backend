<?php

namespace App\Modules\Catalog\Product\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Modules\POS\Order\Models\OrderItem;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the branch this product belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the category this product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(\App\Modules\Catalog\Category\Models\Category::class);
    }

    /**
     * Get the modifier mappings for this product.
     */
    public function modifierMappings()
    {
        return $this->hasMany(ProductModifierMapping::class);
    }

    /**
     * Get the order items containing this product.
     */
    public function orderItems()
    {
        return $this->hasMany(\App\Modules\POS\Order\Models\OrderItem::class);
    }
}
