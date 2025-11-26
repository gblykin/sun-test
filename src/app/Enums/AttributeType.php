<?php

declare(strict_types=1);

namespace App\Enums;

enum AttributeType: int
{
    case INTEGER = 1;
    case DECIMAL = 2;
    case BOOLEAN = 3;
    case STRING = 4;
    case LIST = 5;


    /**
     * Get the label for the attribute type.
     */
    public function label(): string
    {
        return match ($this) {
            self::STRING => 'String',
            self::DECIMAL => 'Decimal',
            self::LIST => 'List',
            self::BOOLEAN => 'Boolean',
            self::INTEGER => 'Integer',
        };
    }

    /**
     * Get the slug for the attribute type.
     */
    public function slug(): string
    {
        return match ($this) {
            self::STRING => 'string',
            self::DECIMAL => 'decimal',
            self::LIST => 'list',
            self::BOOLEAN => 'boolean',
            self::INTEGER => 'integer',
        };
    }

    /**
     * Get all attribute types as array.
     *
     * @return array<int, string>
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Get all attribute types with labels.
     *
     * @return array<int, string>
     */
    public static function toArrayWithLabels(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }
        return $result;
    }

    /**
     * Try to create enum from value.
     */
    public static function tryFromValue(int|string $value): ?self
    {
        if (is_string($value)) {
            return match (strtolower($value)) {
                'string' => self::STRING,
                'decimal' => self::DECIMAL,
                'list' => self::LIST,
                'boolean' => self::BOOLEAN,
                'bool' => self::BOOLEAN,
                'integer' => self::INTEGER,
                'int' => self::INTEGER,
                default => null,
            };
        }

        return self::tryFrom($value);
    }
}
