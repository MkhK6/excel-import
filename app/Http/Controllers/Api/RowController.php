<?php

namespace App\Http\Controllers\Api;

use App\Models\ImportedRow;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RowResource;
use App\Http\Controllers\Controller;

class RowController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = ImportedRow::query()
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'items' => RowResource::collection($items)
                ];
            })
            ->values();

        return response()->json($rows);
    }
}
