<?php

namespace App\Services\Import;

use Carbon\Carbon;
use App\DTO\ImportRowDTO;
use Box\Spout\Common\Type;
use App\Models\ImportedRow;
use App\Events\ImportProgress;
use Box\Spout\Common\Entity\Row;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Validation\ValidationException;
use App\Services\Import\Validators\RowValidator;
use Box\Spout\Reader\Common\Creator\ReaderFactory;

class ImportService
{
    private Connection $redis;
    private string $progressKey;

    private const BATCH_SIZE = 500;

    private array $batch = [];

    public function __construct(private RowValidator $validator)
    {
        $this->redis = Redis::connection();
    }

    public function import(string $absolutePath, string $progressKey): void
    {
        if (!file_exists($absolutePath)) {
            throw new \RuntimeException("File {$absolutePath} not found");
        }

        $this->progressKey = $progressKey;
        $this->batch = [];

        try {
            $this->processFile($absolutePath);
            $this->flushBatch();
        } finally {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    public function getProgressKey(): string
    {
        return $this->progressKey;
    }

    private function processFile(string $absolutePath): void
    {
        // Подсчёт количества строк
        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($absolutePath);

        $total = -1;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $total++;
            }
        }
        $reader->close();

        // Обработка данных
        $reader = ReaderFactory::createFromType(Type::XLSX);
        $reader->open($absolutePath);

        $processed = 0;
        $shouldFireEvent = false;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                if ($rowIndex === 1) continue;

                $this->processRow($row, $rowIndex);

                $processed++;

                if ($processed % 100 === 0) {
                    $this->updateProgress($processed, $total);

                    event(new ImportProgress([
                        'progress_key' => $this->progressKey,
                        'processed' => $processed,
                        'total' => $total
                    ]));

                    $shouldFireEvent = true;
                }
            }
        }

        $reader->close();

        if ($processed > 0 && !$shouldFireEvent) {
            $this->updateProgress($processed, $total);

            event(new ImportProgress([
                'progress_key' => $this->progressKey,
                'processed' => $processed,
                'total' => $total
            ]));
        }
    }

    private function processRow(array|Row $row, int $rowNumber): void
    {
        $rowData = is_array($row) ? $row : $row->toArray();

        $dto = new ImportRowDTO(
            id: $rowData[0] ?? null,
            name: $rowData[1] ?? null,
            date: $rowData[2] ?? null,
            rowNumber: $rowNumber
        );

        try {
            $validated = $this->validator->validate($dto);

            $this->batch[] = [
                'external_id' => $validated['id'],
                'name' => $validated['name'],
                'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ];

            if (count($this->batch) >= self::BATCH_SIZE) {
                $this->flushBatch();
            }
        } catch (ValidationException $e) {
            Storage::disk('public')->append(
                'result.txt',
                "{$rowNumber} - " . implode(', ', $e->validator->errors()->all()) . PHP_EOL
            );
        }
    }

    private function flushBatch(): void
    {
        if (!empty($this->batch)) {
            ImportedRow::insert($this->batch);
            $this->batch = [];
        }
    }

    private function updateProgress(int $processed, int $total): void
    {
        $this->redis->set($this->progressKey, "{$processed}/{$total}");
    }
}
