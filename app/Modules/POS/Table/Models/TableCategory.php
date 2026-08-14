<?php

namespace App\Modules\POS\Table\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableCategory extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $guarded = ['id'];

    public function tables()
    {
        return $this->hasMany(\App\Modules\POS\Table\Models\Table::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class, 'branch_id');
    }
}
