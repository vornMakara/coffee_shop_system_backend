<?php

namespace App\Modules\POS\Table\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'tables';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class, 'branch_id');
    }
}
