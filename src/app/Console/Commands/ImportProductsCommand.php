<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\AttributeMapperInterface;
use App\Contracts\ProcessorInterface;
use App\Contracts\ReaderInterface;
use App\Contracts\WriterInterface;
use App\Services\Import\CategoryAttributeMapper;
use App\Services\Import\CSVReader;
use App\Services\Import\DatabaseWriter;
use App\Services\Import\ProductProcessor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import 
                            {file : Path to CSV file}
                            {--category= : Category slug (battery, solar-panel, connector)}
                            {--chunk=100 : Number of rows to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from CSV file';

    private ReaderInterface $reader;
    private ProcessorInterface $processor;
    private WriterInterface $writer;
    private AttributeMapperInterface $attributeMapper;
    private string $categorySlug;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');
        $chunkSize = (int) $this->option('chunk');

        // Determine category
        $this->categorySlug = $this->determineCategory($filePath);

        if (empty($this->categorySlug)) {
            $this->error("Could not determine category. Please specify --category option.");
            return Command::FAILURE;
        }

        $this->info("Starting import from: {$filePath}");
        $this->info("Category: {$this->categorySlug}");
        $this->info("Chunk size: {$chunkSize}");

        // Initialize services
        $this->initializeServices();

        // Statistics
        $totalProcessed = 0;
        $totalSuccess = 0;
        $totalErrors = 0;
        $startTime = microtime(true);

        try {
            // Read and process in chunks
            foreach ($this->reader->read($filePath, $chunkSize) as $chunk) {
                $this->info("Processing chunk of " . count($chunk) . " rows...");

                $processedChunk = [];
                $chunkErrors = 0;

                foreach ($chunk as $row) {
                    $totalProcessed++;

                    try {
                        $processed = $this->processor->process($row);
                        $processedChunk[] = $processed;
                    } catch (\Exception $e) {
                        $chunkErrors++;
                        $totalErrors++;
                        $this->warn("Error processing row: {$e->getMessage()}");
                        Log::warning("Failed to process row during import", [
                            'row' => $row,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                // Write processed chunk
                if (!empty($processedChunk)) {
                    $successCount = $this->writer->writeBatch($processedChunk);
                    $totalSuccess += $successCount;
                    $failedInChunk = count($processedChunk) - $successCount;
                    $totalErrors += $failedInChunk;

                    if ($failedInChunk > 0) {
                        $this->warn("Failed to write {$failedInChunk} products in this chunk");
                    }
                }

                if ($chunkErrors > 0) {
                    $this->warn("Skipped {$chunkErrors} rows due to errors in this chunk");
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            // Summary
            $this->newLine();
            $this->info("=== Import Summary ===");
            $this->info("Total processed: {$totalProcessed}");
            $this->info("Successfully imported: {$totalSuccess}");
            $this->info("Errors: {$totalErrors}");
            $this->info("Duration: {$duration}s");

            Log::info("Product import completed", [
                'file' => $filePath,
                'category' => $this->categorySlug,
                'total_processed' => $totalProcessed,
                'total_success' => $totalSuccess,
                'total_errors' => $totalErrors,
                'duration' => $duration,
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Fatal error during import: {$e->getMessage()}");
            Log::error("Fatal error during product import", [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Determine category from file path or option.
     *
     * @param string $filePath
     * @return string|null
     */
    private function determineCategory(string $filePath): ?string
    {
        // Check if category is explicitly provided
        $categoryOption = $this->option('category');
        if (!empty($categoryOption)) {
            return $categoryOption;
        }

        // Try to determine from filename
        $filename = basename($filePath, '.csv');
        $filename = strtolower($filename);

        $categoryMap = [
            'batteries' => 'battery',
            'battery' => 'battery',
            'solar_panels' => 'solar-panel',
            'solar-panels' => 'solar-panel',
            'solar_panel' => 'solar-panel',
            'solar-panel' => 'solar-panel',
            'connectors' => 'connector',
            'connector' => 'connector',
        ];

        return $categoryMap[$filename] ?? null;
    }

    /**
     * Initialize service instances.
     *
     * @return void
     */
    private function initializeServices(): void
    {
        $this->attributeMapper = new CategoryAttributeMapper();
        $this->reader = new CSVReader();
        $this->processor = new ProductProcessor($this->attributeMapper, $this->categorySlug);
        $this->writer = new DatabaseWriter($this->attributeMapper);
    }
}

