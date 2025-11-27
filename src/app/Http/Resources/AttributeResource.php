<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\AttributeType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => [
                'id' => $this->type_id,
                'name' => AttributeType::tryFrom($this->type_id)?->label(),
                'slug' => AttributeType::tryFrom($this->type_id)?->slug(),
            ],
            'unit' => $this->unit,
            'description' => $this->description,
            'options' => AttributeOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}

