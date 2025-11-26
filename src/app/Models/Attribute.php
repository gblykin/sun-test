<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type_id',
        'unit',
        'description',
    ];

    /**
     * Get the categories that have this attribute.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attribute')
            ->withPivot('sort_order');
    }

    /**
     * Get the attribute options.
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    /**
     * Get the product attribute values.
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}

