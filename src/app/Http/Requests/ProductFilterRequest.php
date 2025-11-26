<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
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
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'manufacturer_id' => 'sometimes|integer|exists:manufacturers,id',
            'price_min' => 'sometimes|numeric|min:0',
            'price_max' => 'sometimes|numeric|min:0|gte:price_min',
            'attributes' => 'sometimes|array',
            'attributes.*.attribute_id' => 'required_with:attributes|integer|exists:attributes,id',
            'attributes.*.value' => 'required_with:attributes',
            'limit' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ];
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
            'manufacturer_id.exists' => 'The selected manufacturer does not exist.',
            'price_min.numeric' => 'The minimum price must be a number.',
            'price_max.numeric' => 'The maximum price must be a number.',
            'price_max.gte' => 'The maximum price must be greater than or equal to the minimum price.',
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
        return $this->only([
            'category_id',
            'manufacturer_id',
            'price_min',
            'price_max',
            'attributes',
        ]);
    }

    /**
     * Get pagination limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return (int) $this->input('limit', 10);
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

