<?php

namespace App\Modules\Catalog\Modifier\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModifierResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_required' => $this->is_required,
            'min_selections' => $this->min_selections,
            'max_selections' => $this->max_selections,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'options' => $this->whenLoaded('options'),
        ];
    }
}
