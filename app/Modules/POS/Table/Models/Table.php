<?php

namespace App\Modules\POS\Table\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Traits\HasUuid;

class Table extends Model
{
    use HasUuid;

    protected $table = 'tables';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // Based on schema, we only have created_at. Let's disable default timestamps and handle if needed. Or keep if we add updated_at.

    protected $guarded = ['id'];

    public function branch()
    {
        return $this->belongsTo(\App\Modules\Auth\Branch\Models\Branch::class, 'branch_id');
    }
}
