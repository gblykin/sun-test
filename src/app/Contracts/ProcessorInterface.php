<?php

declare(strict_types=1);

namespace App\Contracts;

interface ProcessorInterface
{
    /**
     * Process raw data into structured format.
     *
     * @param array $rawData Raw data row
     * @return array Processed data
     */
    public function process(array $rawData): array;
}

