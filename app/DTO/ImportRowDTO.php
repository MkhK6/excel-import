<?php

namespace App\DTO;

class ImportRowDTO
{
    public function __construct(
        public mixed $id,
        public mixed $name,
        public mixed $date,
        public int $rowNumber
    ) {}
}
