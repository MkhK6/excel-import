<?php

namespace App\DTO;

class ImportRowDTO
{
    public function __construct(
        public readonly mixed $id,
        public readonly mixed $name,
        public readonly mixed $date,
        public readonly int $rowNumber
    ) {}
}
