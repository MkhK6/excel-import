<?php

namespace Tests\Unit\Http\Controllers;

use Tests\TestCase;
use ReflectionClass;
use App\Jobs\ImportJob;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\ImportRequest;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ImportController;

class ImportControllerTest extends TestCase
{
    private ImportController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ImportController($this->mockImportService());
    }

    public function test_import_dispatches_job_and_returns_progress_key()
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.xlsx', 100);

        $request = new ImportRequest([], [], [], [], ['file' => $file]);

        $response = $this->controller->import($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertArrayHasKey('progress_key', $response->getData(true));

        Queue::assertPushed(ImportJob::class, function (ImportJob $job) {
            $reflector = new ReflectionClass($job);

            $filePath = $reflector->getProperty('filePath');
            $filePath->setAccessible(true);

            $progressKey = $reflector->getProperty('progressKey');
            $progressKey->setAccessible(true);

            return str_contains($filePath->getValue($job), 'temp_imports') &&
                str_contains($progressKey->getValue($job), 'import_progress_');
        });
    }

    private function mockImportService()
    {
        return $this->createMock(\App\Services\Import\ImportService::class);
    }
}
