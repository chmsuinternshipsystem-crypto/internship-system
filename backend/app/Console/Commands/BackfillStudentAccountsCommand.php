<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class BackfillStudentAccountsCommand extends Command
{
    protected $signature = 'students:backfill-accounts {--reset-default : Reset existing student account passwords to student_number}';

    protected $description = 'Create missing student_accounts using default password = student_number.';

    public function handle(): int
    {
        $created = 0;
        $reset = 0;
        $resetDefault = (bool) $this->option('reset-default');

        $students = Student::query()->with('account')->orderBy('id')->get();
        foreach ($students as $student) {
            $account = $student->account;

            if (! $account) {
                StudentAccount::create([
                    'student_id' => $student->id,
                    'email' => null,
                    'password' => Hash::make((string) $student->student_number),
                    'is_active' => true,
                ]);
                $created++;

                continue;
            }

            if ($resetDefault) {
                $account->password = Hash::make((string) $student->student_number);
                $account->save();
                $reset++;
            }
        }

        $this->info("Backfill done. Created: {$created}. Reset: {$reset}.");

        return self::SUCCESS;
    }
}
