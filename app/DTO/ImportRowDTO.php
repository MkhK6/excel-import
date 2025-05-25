<?php

namespace App\DTO;

class ImportRowDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $date,
        public int $rowNumber
    ) {}
}
