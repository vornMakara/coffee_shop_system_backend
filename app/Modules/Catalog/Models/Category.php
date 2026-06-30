<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Auth\Models\Branch;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'parent_id',
        'name',
        'sort_order',
        'is_active',
    ];

    /**
     * Get the branch this category belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the products in this category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
