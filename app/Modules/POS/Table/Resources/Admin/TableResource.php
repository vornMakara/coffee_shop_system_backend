<?php

namespace App\Modules\POS\Table\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Branch\Resources\Admin\BranchResource;

class TableResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'seating_capacity' => $this->seating_capacity,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}
