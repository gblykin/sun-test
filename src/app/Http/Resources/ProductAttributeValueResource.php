<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttributeValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attribute' => new AttributeResource($this->whenLoaded('attribute')),
            'attribute_option' => new AttributeOptionResource($this->whenLoaded('attributeOption')),
            'value_text' => $this->value_text,
            'value_decimal' => $this->value_decimal,
        ];
    }
}

