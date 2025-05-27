<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Api\RowController;

Route::post('/import', [ImportController::class, 'import']);
Route::get('/rows', [RowController::class, 'index']);
