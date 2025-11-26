<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Contracts\ReaderInterface;
use Generator;
use Illuminate\Support\Facades\Log;

class CSVReader implements ReaderInterface
{
    /**
     * Read CSV file in chunks.
     *
     * @param string $source CSV file path
     * @param int $chunkSize Number of rows per chunk
     * @return Generator Yields arrays of rows
     */
    public function read(string $source, int $chunkSize = 100): Generator
    {
        if (!file_exists($source)) {
            Log::error("CSV file not found: {$source}");
            return;
        }

        $handle = fopen($source, 'r');
        if ($handle === false) {
            Log::error("Failed to open CSV file: {$source}");
            return;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            Log::error("Failed to read CSV header from: {$source}");
            return;
        }

        $chunk = [];
        $rowNumber = 1; // Start from 1 (header is row 0)

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Combine headers with row data
            $data = array_combine($headers, $row);
            if ($data === false) {
                Log::warning("Row {$rowNumber} has incorrect number of columns in: {$source}");
                continue;
            }

            $chunk[] = $data;

            // Yield chunk when it reaches the specified size
            if (count($chunk) >= $chunkSize) {
                yield $chunk;
                $chunk = [];
            }
        }

        // Yield remaining rows
        if (!empty($chunk)) {
            yield $chunk;
        }

        fclose($handle);
    }
}

