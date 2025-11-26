<?php

declare(strict_types=1);

namespace App\Contracts;

interface WriterInterface
{
    /**
     * Write processed data.
     *
     * @param array $data Processed data
     * @return bool Success status
     */
    public function write(array $data): bool;

    /**
     * Write multiple records in batch.
     *
     * @param array $dataArray Array of processed data
     * @return int Number of successfully written records
     */
    public function writeBatch(array $dataArray): int;
}

