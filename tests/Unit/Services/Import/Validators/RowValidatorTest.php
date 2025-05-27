<?php

namespace Tests\Unit\Services\Import\Validators;

use Tests\TestCase;
use App\DTO\ImportRowDTO;
use App\Services\Import\Validators\RowValidator;
use Illuminate\Validation\ValidationException;

class RowValidatorTest extends TestCase
{
    private RowValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RowValidator();
    }

    public function test_validate_returns_valid_data()
    {
        $dto = new ImportRowDTO(
            id: 1,
            name: 'Test Name',
            date: '2023-01-01',
            rowNumber: 1
        );

        $result = $this->validator->validate($dto);

        $this->assertEquals([
            'id' => 1,
            'name' => 'Test Name',
            'date' => '2023-01-01',
        ], $result);
    }

    public function test_validate_throws_exception_for_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $dto = new ImportRowDTO(
            id: null,
            name: null,
            date: 'invalid-date',
            rowNumber: 1
        );

        $this->validator->validate($dto);
    }

    public function test_invalid_dates_are_rejected()
    {
        $invalidDates = [
            'not-a-date',
            '2023-13-01',
            '32.01.2023'
        ];

        foreach ($invalidDates as $date) {
            $dto = new ImportRowDTO(
                id: 1,
                name: 'Test Name',
                date: $date,
                rowNumber: 1
            );

            try {
                $this->validator->validate($dto);
                $this->fail("Expected ValidationException for date: {$date}");
            } catch (ValidationException $e) {
                $this->assertStringContainsString('Неверный формат даты', $e->getMessage());
            }
        }
    }

    public function test_valid_dates_are_accepted()
    {
        $validDates = [
            '2023-01-01',
            '01.01.2023',
            '2023/01/01'
        ];

        foreach ($validDates as $date) {
            $dto = new ImportRowDTO(
                id: 1,
                name: 'Test Name',
                date: $date,
                rowNumber: 1
            );

            $result = $this->validator->validate($dto);
            $this->assertEquals($date, $result['date']);
        }
    }
}
