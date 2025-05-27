<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ImportJob;
use App\Services\Import\ImportService;

class ImportJobTest extends TestCase
{
    public function test_job_handles_import_correctly()
    {
        $filePath = '/test.xlsx';
        $progressKey = 'import_progress_111';

        $importService = $this->createMock(ImportService::class);
        $importService->expects($this->once())
            ->method('import')
            ->with($filePath, $progressKey);

        $job = new ImportJob($filePath, $progressKey);
        $job->handle($importService);
    }
}
