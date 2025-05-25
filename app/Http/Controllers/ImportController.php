<?php

namespace App\Http\Controllers;

use App\Jobs\ImportJob;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ImportRequest;
use App\Services\Import\ImportService;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function __construct(private ImportService $importService) {}

    public function import(ImportRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $tempPath = $file->storeAs('temp_imports', uniqid() . '.xlsx');

        $absolutePath = Storage::path($tempPath);

        $progressKey = 'import:progress:' . uniqid();

        ImportJob::dispatch($absolutePath, $progressKey)->onQueue('imports');

        return response()->json([
            'progress_key' => $progressKey
        ]);
    }
}
