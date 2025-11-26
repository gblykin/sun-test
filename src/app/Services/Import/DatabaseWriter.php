<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\AttributeMapperInterface;
use App\Contracts\WriterInterface;
use App\Enums\AttributeType;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseWriter implements WriterInterface
{
    public function __construct(
        private readonly AttributeMapperInterface $attributeMapper
    ) {
    }
    /**
     * Write processed product data to database.
     *
     * @param array $data Processed data with 'product' and 'attributes' keys
     * @return bool Success status
     */
    public function write(array $data): bool
    {
        try {
            DB::beginTransaction();

            $productData = $data['product'];
            $attributes = $data['attributes'] ?? [];

            // Find or create product by external_id
            $product = null;
            if (!empty($productData['external_id'])) {
                $product = Product::where('external_id', '=', $productData['external_id'])->first();
            }

            if ($product === null) {
                // Create new product
                $product = Product::create($productData);
                Log::info("Created product: {$product->title}", [
                    'product_id' => $product->id,
                    'external_id' => $productData['external_id'],
                ]);
            } else {
                // Update existing product
                $product->update($productData);
                Log::info("Updated product: {$product->title}", [
                    'product_id' => $product->id,
                    'external_id' => $productData['external_id'],
                ]);

                // Delete existing attribute values
                ProductAttributeValue::where('product_id', '=', $product->id)->delete();
            }

            // Save attribute values
            foreach ($attributes as $attributeData) {
                $this->saveAttributeValue($product->id, $attributeData);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to write product to database", [
                'product_data' => $data['product'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Write multiple records in batch.
     *
     * @param array $dataArray Array of processed data
     * @return int Number of successfully written records
     */
    public function writeBatch(array $dataArray): int
    {
        $successCount = 0;

        foreach ($dataArray as $data) {
            if ($this->write($data)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Save attribute value for product.
     *
     * @param int $productId Product ID
     * @param array $attributeData Attribute data with 'attribute_id', 'value', 'type'
     * @return void
     */
    private function saveAttributeValue(int $productId, array $attributeData): void
    {
        $valueData = [
            'product_id' => $productId,
            'attribute_id' => $attributeData['attribute_id'],
            'attribute_option_id' => null,
            'value_text' => null,
            'value_decimal' => null,
        ];

        $type = $attributeData['type'];
        $value = $attributeData['value'];
        $attributeId = $attributeData['attribute_id'];

        match ($type) {
            AttributeType::DECIMAL, AttributeType::INTEGER => $valueData['value_decimal'] = (float) $value,
            AttributeType::STRING, AttributeType::BOOLEAN => $valueData['value_text'] = (string) $value,
            AttributeType::LIST => $valueData['attribute_option_id'] = $this->attributeMapper->getOrCreateAttributeOption($attributeId, (string) $value),
        };

        ProductAttributeValue::create($valueData);
    }
}

