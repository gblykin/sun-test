<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
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
            'q' => 'required|string|min:1|max:255',
            'limit' => 'sometimes|integer|min:1|max:100',
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
            'q.required' => 'The search query is required.',
            'q.min' => 'The search query must be at least 1 character.',
            'q.max' => 'The search query may not be greater than 255 characters.',
            'limit.max' => 'The limit may not be greater than 100.',
        ];
    }

    /**
     * Get search query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->input('q');
    }

    /**
     * Get search limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return (int) $this->input('limit', 10);
    }
}

