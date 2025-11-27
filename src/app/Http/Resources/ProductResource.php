<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attributeValues = $this->whenLoaded('attributeValues');
        
        // Filter attribute values to only include those from product's category
        $categoryAttributeIds = [];
        if ($this->relationLoaded('category') && $this->category) {
            // Get attribute IDs from loaded category attributes
            if ($this->category->relationLoaded('attributes')) {
                $categoryAttributeIds = $this->category->attributes->pluck('id')->toArray();
            } else {
                // Fallback: load attributes if not already loaded
                $categoryAttributeIds = $this->category->attributes()->pluck('id')->toArray();
            }
        }
        
        $filteredAttributeValues = collect($attributeValues)
            ->filter(function ($attrValue) use ($categoryAttributeIds) {
                if (empty($categoryAttributeIds)) {
                    return true; // If no category loaded, show all
                }
                return $attrValue->relationLoaded('attribute') 
                    && $attrValue->attribute 
                    && in_array($attrValue->attribute->id, $categoryAttributeIds);
            })
            ->take(3) // Max 3 attributes
            ->values(); // Re-index array
        
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => $this->price,
            'description' => $this->description,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'manufacturer' => new ManufacturerResource($this->whenLoaded('manufacturer')),
            'attribute_values' => ProductAttributeValueResource::collection($filteredAttributeValues),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

