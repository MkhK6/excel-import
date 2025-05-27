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
        ], [
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
            'date' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidDate($value)) {
                        $fail("Неверный формат даты");
                    }
                }
            ]
        ], [
            'required' => 'Поле обязательно для заполнения',
            'integer' => 'Должно быть целым числом',
            'string' => 'Должно быть строкой',
            'max' => 'Максимальная длина :max символов',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $validated['date'] = $this->normalizeDate($validated['date']);

        return $validated;
    }

    private function isValidDate(string $date): bool
    {
        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function normalizeDate(string $date): string
    {
        try {
            return Carbon::parse($date)->format('d.m.Y');
        } catch (\Exception $e) {
            return $date;
        }
    }
}
