<?php

declare(strict_types=1);

namespace App\Config;

use App\Enums\AttributeType;

class AttributeMappingConfig
{
    /**
     * Get category title mapping.
     * Maps category slug to category title.
     *
     * @return array<string, string>
     */
    public static function getCategoryTitles(): array
    {
        return [
            'battery' => 'Batteries',
            'solar-panel' => 'Solar Panels',
            'connector' => 'Connectors',
        ];
    }

    /**
     * Get attribute mapping configuration.
     * Maps CSV column names to attribute configuration (slug and type).
     *
     * @return array<string, array<string, array{slug: string, type: AttributeType}>>
     */
    public static function getMapping(): array
    {
        return [
            'battery' => [
                'capacity' => [
                    'slug' => 'capacity',
                    'type' => AttributeType::DECIMAL,
                    'title' => 'Capacity',
                    'unit' => 'kWh',
                ],
            ],
            'solar-panel' => [
                'power_output' => [
                    'slug' => 'power-output',
                    'type' => AttributeType::DECIMAL,
                    'title' => 'Power Output',
                    'unit' => 'W',
                ],
            ],
            'connector' => [
                'connector_type' => [
                    'slug' => 'connector-type',
                    'type' => AttributeType::LIST,
                    'title' => 'Connector Type',
                    'unit' => null,
                ],
            ],
        ];
    }    

    /**
     * Get category title by slug.
     *
     * @param string $slug Category slug
     * @return string Category title
     */
    public static function getCategoryTitle(string $slug): string
    {
        return self::getCategoryTitles()[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
    }

    /**
     * Get mapping for a specific category.
     *
     * @param string $categorySlug Category slug
     * @return array<string, array{slug: string, type: AttributeType, title: string, unit: string|null}>
     */
    public static function getMappingForCategory(string $categorySlug): array
    {
        return self::getMapping()[$categorySlug] ?? [];
    }
}

