<?php

declare(strict_types=1);

namespace App\Contracts;

use Generator;

interface ReaderInterface
{
    /**
     * Read data in chunks.
     *
     * @param string $source Data source path
     * @param int $chunkSize Number of rows per chunk
     * @return Generator Yields arrays of rows
     */
    public function read(string $source, int $chunkSize = 100): Generator;
}

