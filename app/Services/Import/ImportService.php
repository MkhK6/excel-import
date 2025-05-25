<?php

namespace App\Services\Import;

use Carbon\Carbon;
use App\DTO\ImportRowDTO;
use App\Models\ImportedRow;
use App\Events\ImportProgress;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Validation\ValidationException;
use App\Services\Import\Validators\RowValidator;

class ImportService
{
    private Connection $redis;
    private string $progressKey;
    private array $errors = [];

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
        $this->errors = [];

        try {
            $this->processFile($absolutePath);
            $this->storeErrorReport();
        } finally {
            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    private function processFile(string $absolutePath): void
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($absolutePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $processed = 0;
        $total = count($rows) - 1;

        foreach ($rows as $index => $row) {
            /** 
             * TODO удалить sleep() 
             */
            sleep(2);

            if ($index === 0) continue;

            try {
                $this->processRow($row, $index + 1);
            } catch (ValidationException $e) {
                $this->errors[$index + 1] = $e->errors();
            }

            $processed++;

            event(new ImportProgress([
                'processed' => $processed,
                'total' => $total
            ]));

            $this->updateProgress($processed, $total);
        }
    }

    protected function normalizeDate(string $dateValue, $rowNumber): ?string
    {
        try {
            $date = Carbon::parse($dateValue);
            return $date->format('d.m.Y');
        } catch (\Exception $e) {
            $this->errors[$rowNumber][] = ['date' => ['Invalid date format']];
            return null;
        }
    }

    private function processRow(array $row, int $rowNumber): void
    {
        $dateValue = $row[2] ?? '';
        $normalizedDate = $this->normalizeDate($dateValue, $rowNumber);

        if ($normalizedDate === null) {
            return;
        }

        $dto = new ImportRowDTO(
            id: $row[0] ?? null,
            name: $row[1] ?? '',
            date: $normalizedDate,
            rowNumber: $rowNumber
        );

        $validated = $this->validator->validate($dto);

        $date = \Carbon\Carbon::createFromFormat('d.m.Y', $validated['date']);

        ImportedRow::create([
            'external_id' => $validated['id'],
            'name' => $validated['name'],
            'date' => $date->format('Y-m-d')
        ]);
    }

    private function updateProgress(int $processed, int $total): void
    {
        $this->redis->set($this->progressKey, "{$processed}/{$total}");
    }

    private function storeErrorReport(): void
    {
        $reportContent = '';

        foreach ($this->errors as $rowNumber => $errors) {
            $errorMessages = [];

            foreach ($errors as $fieldErrors) {
                $errorMessages = array_merge($errorMessages, $fieldErrors);
            }

            $reportContent .= "{$rowNumber} - " . implode(', ', $errorMessages) . PHP_EOL;
        }

        Storage::disk('public')->put('result.txt', $reportContent);
    }

    public function getProgressKey(): string
    {
        return $this->progressKey;
    }
}
