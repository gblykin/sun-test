<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\AttributeMapperInterface;
use App\Contracts\ProcessorInterface;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly AttributeMapperInterface $attributeMapper,
        private readonly string $categorySlug
    ) {
    }

    /**
     * Process raw CSV data into structured product data.
     *
     * @param array $rawData Raw CSV row
     * @return array Processed product data
     */
    public function process(array $rawData): array
    {
        try {
            // Get or create category
            $category = Category::where('slug', '=', $this->categorySlug)->first();
            if ($category === null) {
                $categoryTitle = \App\Config\AttributeMappingConfig::getCategoryTitle($this->categorySlug);
                $category = Category::create([
                    'slug' => $this->categorySlug,
                    'title' => $categoryTitle,
                ]);

                Log::info("Created new category: {$this->categorySlug} ({$categoryTitle})", [
                    'category_id' => $category->id,
                ]);
            }

            // Get manufacturer (create if not exists)
            $manufacturerTitle = $rawData['manufacturer'] ?? null;
            if (empty($manufacturerTitle)) {
                throw new \RuntimeException("Manufacturer is required");
            }

            $manufacturerSlug = Str::slug($manufacturerTitle);
            $manufacturer = \App\Models\Manufacturer::firstOrCreate(
                ['slug' => $manufacturerSlug],
                ['title' => $manufacturerTitle]
            );

            // Process product data
            $productData = [
                'external_id' => $rawData['id'] ?? null,
                'title' => $rawData['name'] ?? '',
                'category_id' => $category->id,
                'manufacturer_id' => $manufacturer->id,
                'slug' => $this->generateUniqueSlug($rawData['name'] ?? ''),
                'price' => (float) ($rawData['price'] ?? 0),
                'description' => $rawData['description'] ?? '',
            ];

            // Process attributes
            $attributeMapping = $this->attributeMapper->getMapping($this->categorySlug);
            $attributes = [];

            foreach ($attributeMapping as $csvColumn => $config) {
                if (!isset($rawData[$csvColumn]) || $rawData[$csvColumn] === '') {
                    continue;
                }

                $value = $rawData[$csvColumn];
                $attributeId = $this->attributeMapper->getOrCreateAttribute(
                    $config['slug'],
                    $config['type'],
                    $config['title'],
                    $config['unit'] ?? null
                );

                // Link attribute to category
                $this->attributeMapper->linkAttributeToCategory($category->id, $attributeId);

                $attributes[] = [
                    'attribute_id' => $attributeId,
                    'value' => $value,
                    'type' => $config['type'],
                ];
            }

            return [
                'product' => $productData,
                'attributes' => $attributes,
            ];
        } catch (\Exception $e) {
            Log::error("Failed to process product data", [
                'raw_data' => $rawData,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique slug for product.
     *
     * @param string $title Product title
     * @return string Unique slug
     */
    private function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (\App\Models\Product::where('slug', '=', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

