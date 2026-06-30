<?php

namespace App\Modules\Catalog\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModifierMapping extends Model
{
    use HasFactory, HasUuid;

    // Use specific table name since it doesn't follow standard pluralization
    protected $table = 'product_modifier_mapping';

    protected $fillable = [
        'product_id',
        'modifier_group_id',
    ];

    /**
     * Get the product this mapping belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the modifier group this mapping belongs to.
     */
    public function modifierGroup()
    {
        return $this->belongsTo(ModifierGroup::class);
    }
}
