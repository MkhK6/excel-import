<?php

namespace App\Http\Controllers\Api;

use App\Models\ImportedRow;
use Illuminate\Http\JsonResponse;
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
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'external_id' => $item->external_id,
                            'name' => $item->name,
                            'created_at' => $item->created_at->toDateTimeString(),
                            'updated_at' => $item->updated_at->toDateTimeString()
                        ];
                    })
                ];
            })
            ->values();

        return response()->json($rows);
    }
}
