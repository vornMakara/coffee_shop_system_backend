<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Auth\Models\Branch;
use App\Modules\POS\Models\OrderItem;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'category_id',
        'name',
        'selling_price',
        'is_active',
    ];

    /**
     * Get the branch this product belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the category this product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
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
        return $this->hasMany(OrderItem::class);
    }
}
