<?php

namespace App\Services\Import\Validators;

use Carbon\Carbon;
use App\DTO\ImportRowDTO;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RowValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(ImportRowDTO $dto): array
    {
        $validator = Validator::make([
            'id' => $dto->id,
            'name' => $dto->name,
            'date' => $dto->date,
            'row_number' => $dto->rowNumber
        ], [
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'date' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        Carbon::createFromFormat('d.m.Y', $value);
                    } catch (\Exception $e) {
                        $fail("The $attribute has invalid format. Expected d.m.Y");
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
