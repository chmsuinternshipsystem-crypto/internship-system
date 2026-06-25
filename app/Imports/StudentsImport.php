<?php

namespace App\Imports;

use App\Models\Deployment;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Support\PhoneHelper;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class StudentsImport implements SkipsOnFailure, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    private int $createdCount = 0;

    private int $updatedCount = 0;

    private int $skippedCount = 0;

    private int $duplicateNameWarnings = 0;

    private int $totalValidRows = 0;

    private bool $allRowsAlreadyExisting = true;

    private array $currentRow = [];

    private static ?array $studentCache = null;

    private static function loadCaches(): void
    {
        if (self::$studentCache === null) {
            self::$studentCache = Student::pluck('id', 'student_number')->all();
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        $this->currentRow = $row;

        $studentNumber = trim((string) ($row['student_number'] ?? $row['student number'] ?? ''));
        if ($studentNumber === '') {
            $this->skippedCount++;

            return null;
        }

        $studentNumber = preg_replace('/\D+/', '', $studentNumber);
        if (strlen($studentNumber) !== 8) {
            $this->skippedCount++;

            return null;
        }

        $this->totalValidRows++;
        self::loadCaches();

        $existingStudentId = self::$studentCache[$studentNumber] ?? null;
        $existingStudent = $existingStudentId ? Student::find($existingStudentId) : null;

        if (! $existingStudent) {
            $firstName = trim((string) ($row['first_name'] ?? $row['first name'] ?? ''));
            $lastName = trim((string) ($row['last_name'] ?? $row['last name'] ?? ''));
            if ($firstName !== '' && $lastName !== '' && strlen($firstName) > 2 && strlen($lastName) > 2) {
                $sameName = Student::where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->exists();
                if ($sameName) {
                    $this->duplicateNameWarnings++;
                }
            }
        }

        $email = trim((string) ($row['email'] ?? ''));
        $data = [
            'last_name' => trim((string) ($row['last_name'] ?? $row['last name'] ?? '')),
            'first_name' => trim((string) ($row['first_name'] ?? $row['first name'] ?? '')),
            'middle_name' => trim((string) ($row['middle_name'] ?? $row['middle name'] ?? '')),
            'name_extension' => trim((string) ($row['name_extension'] ?? $row['name extension'] ?? '')),
            'student_number' => $studentNumber,
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => strtoupper(trim((string) ($row['section'] ?? 'A'))),
            'contact_number' => $this->sanitizeContactNumber($row['contact_number'] ?? $row['contact number'] ?? ''),
        ];

        if ($existingStudent) {
            $existingStudent->fill($data);
            $existingStudent->save();
            $this->updatedCount++;

            $account = $existingStudent->account;
            if ($account) {
                if ($email !== '') {
                    $account->email = strtolower($email);
                }
                $account->save();
            }

            return $existingStudent;
        }

        $student = Student::create($data);
        $this->createdCount++;
        $this->allRowsAlreadyExisting = false;

        StudentAccount::create([
            'student_id' => $student->id,
            'email' => $email !== '' ? strtolower($email) : null,
            'password' => Hash::make($studentNumber),
            'is_active' => true,
            'first_login' => true,
        ]);

        Deployment::create([
            'student_id' => $student->id,
            'company_id' => null,
            'start_date' => today(),
            'status' => 'pending',
        ]);

        return $student;
    }

    public function rules(): array
    {
        return [
            'student_number' => 'required|digits:8',
            'last_name' => 'required|string|max:120',
            'first_name' => 'required|string|max:120',
            'section' => 'required|string|max:10',
            'contact_number' => 'required|regex:/^09[0-9]{9}$/',
            'email' => 'nullable|email|max:255|unique:student_accounts,email',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->skippedCount += count($failures);
    }

    private function sanitizeContactNumber($number): ?string
    {
        return PhoneHelper::normalizeMobile((string) $number);
    }

    public function getCreatedCount(): int
    {
        return $this->createdCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getDuplicateNameWarnings(): int
    {
        return $this->duplicateNameWarnings;
    }

    public function allRowsAlreadyExisting(): bool
    {
        return $this->totalValidRows > 0 && $this->allRowsAlreadyExisting;
    }
}
