<?php

namespace App\Modules\POS\Customer\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class, 'branch_id');
    }
}
