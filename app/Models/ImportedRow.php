<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportedRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'date'
    ];

    protected $casts = [
        'date' => 'date'
    ];
}
