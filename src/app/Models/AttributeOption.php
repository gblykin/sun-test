<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttributeOption extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'attribute_id',
        'label',
        'sort_order',
    ];

    /**
     * Get the attribute that owns the option.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the product attribute values.
     */
    public function productAttributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}

