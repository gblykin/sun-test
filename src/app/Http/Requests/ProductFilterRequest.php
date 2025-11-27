<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Parsers\AttributeValueParserFactory;
use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    /**
     * Get the parser factory instance.
     *
     * @return AttributeValueParserFactory
     */
    private function getParserFactory(): AttributeValueParserFactory
    {
        return app(AttributeValueParserFactory::class);
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Dynamic rules for attribute slugs - will be validated in getFilters()
        $rules = [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'manufacturer' => 'sometimes|string', // Format: "1,2,3" (comma-separated IDs)
            'price' => 'sometimes|string', // Format: "400:600", "400:", ":600"
            'limit' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ];

        // Add rules for all possible attribute slugs (they come as query params like power-output=400:600)
        // We'll validate them in getFilters() method
        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'manufacturer.string' => 'The manufacturer must be in format "1,2,3" (comma-separated IDs).',
            'price.string' => 'The price must be in format "min:max", ":max", "min:", or a single value.',
            'attributes.*.attribute_id.exists' => 'One or more selected attributes do not exist.',
            'limit.max' => 'The limit may not be greater than :max.',
        ];
    }

    /**
     * Get filter criteria from request.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        $filters = $this->only(['category_id']);

        $this->parseManufacturerFilter($filters);
        $this->parsePriceFilter($filters);
        $this->parseAttributeFilters($filters);

        return $filters;
    }

    /**
     * Parse manufacturer filter from request.
     * Format: "1,2,3" (comma-separated IDs)
     *
     * @param array<string, mixed> $filters
     * @return void
     */
    private function parseManufacturerFilter(array &$filters): void
    {
        $manufacturer = $this->input('manufacturer');
        if (empty($manufacturer)) {
            return;
        }

        $manufacturerIds = array_map('trim', explode(',', $manufacturer));
        $manufacturerIds = array_filter($manufacturerIds, fn($id) => $id !== '' && is_numeric($id));
        
        if (!empty($manufacturerIds)) {
            $filters['manufacturer_id'] = array_map('intval', $manufacturerIds);
        }
    }

    /**
     * Parse price filter from request.
     * Format: "min:max", ":max", "min:", or single value
     *
     * @param array<string, mixed> $filters
     * @return void
     */
    private function parsePriceFilter(array &$filters): void
    {
        $price = $this->input('price');
        if (empty($price)) {
            return;
        }

        if (strpos($price, ':') !== false) {
            // Range format: "min:max", ":max", "min:"
            [$min, $max] = explode(':', $price, 2);
            $min = trim($min) !== '' ? (float) $min : null;
            $max = trim($max) !== '' ? (float) $max : null;
            
            if ($min !== null) {
                $filters['price_min'] = $min;
            }
            if ($max !== null) {
                $filters['price_max'] = $max;
            }
        } else {
            // Single value - treat as exact price or minimum
            $filters['price_min'] = (float) $price;
        }
    }

    /**
     * Parse attribute filters from query parameters.
     * Format: power-output=400:600 (range) or connector-type=1,2,3 (list)
     *
     * @param array<string, mixed> $filters
     * @return void
     */
    private function parseAttributeFilters(array &$filters): void
    {
        $attributes = [];
        $allInput = $this->all();
        
        // Get all attribute slugs from database
        $attributeSlugs = \App\Models\Attribute::pluck('slug')->toArray();

        foreach ($allInput as $key => $value) {
            if (!$this->isAttributeSlug($key, $attributeSlugs) || empty($value)) {
                continue;
            }

            $attribute = \App\Models\Attribute::where('slug', '=', $key)->first();
            if ($attribute === null) {
                continue;
            }

            $parsedValue = $this->parseAttributeValue($value, $attribute->type_id);
            if ($parsedValue !== null) {
                $attributes[] = [
                    'attribute_id' => $attribute->id,
                    'value' => $parsedValue,
                ];
            }
        }
        
        if (!empty($attributes)) {
            $filters['attributes'] = $attributes;
        }
    }

    /**
     * Check if the key is a valid attribute slug.
     *
     * @param string $key
     * @param array<string> $attributeSlugs
     * @return bool
     */
    private function isAttributeSlug(string $key, array $attributeSlugs): bool
    {
        return in_array($key, $attributeSlugs, true);
    }

    /**
     * Parse attribute value from URL format.
     * 
     * @param string $value Value from URL (e.g., "400:600", "1,2,3", ":600", "400:")
     * @param int $typeId Attribute type ID
     * @return array|string|int|float|null Parsed value
     */
    private function parseAttributeValue(string $value, int $typeId): array|string|int|float|null
    {
        $attributeType = \App\Enums\AttributeType::tryFrom($typeId);
        
        if ($attributeType === null) {
            return null;
        }

        $parser = $this->getParserFactory()->create($attributeType);
        
        return $parser->parse($value);
    }

    /**
     * Get pagination limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return (int) $this->input('limit', 12);
    }

    /**
     * Get pagination page.
     *
     * @return int
     */
    public function getPage(): int
    {
        return (int) $this->input('page', 1);
    }
}

