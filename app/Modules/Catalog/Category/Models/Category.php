<?php

namespace App\Modules\Catalog\Category\Models;

use App\Modules\Auth\Branch\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Get the branch this category belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class);
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(\App\Modules\Catalog\Product\Models\Product::class);
    }
}
