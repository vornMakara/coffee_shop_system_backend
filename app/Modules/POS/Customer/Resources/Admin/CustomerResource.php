<?php

namespace App\Modules\POS\Customer\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Auth\Branch\Resources\Admin\BranchResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'loyalty_points' => $this->loyalty_points,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'branch' => new BranchResource($this->whenLoaded('branch')),
        ];
    }
}
