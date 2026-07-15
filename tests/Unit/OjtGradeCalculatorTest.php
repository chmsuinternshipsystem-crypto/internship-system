<?php

namespace Tests\Unit;

use App\Models\Evaluation;
use App\Models\Student;
use App\Support\OjtGradeCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OjtGradeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): Student
    {
        return Student::query()->create([
            'name' => 'Calc Student',
            'student_number' => '20239999',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);
    }

    public function test_computes_weighted_final_when_both_scores_exist(): void
    {
        $student = $this->makeStudent();

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'industry',
            'score' => 70,
            'comments' => null,
            'evaluated_at' => now()->subDay(),
        ]);

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'school',
            'score' => 100,
            'comments' => null,
            'evaluated_at' => now(),
        ]);

        $summary = OjtGradeCalculator::summary($student);

        $this->assertTrue($summary['is_complete']);
        $this->assertSame(70, $summary['hte_score']);
        $this->assertSame(100, $summary['instructor_score']);
        $this->assertEqualsWithDelta(79.0, $summary['final_grade'], 0.001);
    }

    public function test_incomplete_when_missing_school(): void
    {
        $student = $this->makeStudent();
        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'industry',
            'score' => 80,
            'comments' => null,
            'evaluated_at' => now(),
        ]);

        $summary = OjtGradeCalculator::summary($student);
        $this->assertFalse($summary['is_complete']);
        $this->assertNull($summary['final_grade']);
    }

    public function test_latest_industry_wins(): void
    {
        $student = $this->makeStudent();

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'industry',
            'score' => 50,
            'comments' => null,
            'evaluated_at' => now()->subDays(2),
        ]);

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'industry',
            'score' => 90,
            'comments' => null,
            'evaluated_at' => now()->subDay(),
        ]);

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'school',
            'score' => 100,
            'comments' => null,
            'evaluated_at' => now(),
        ]);

        $summary = OjtGradeCalculator::summary($student);
        $this->assertSame(90, $summary['hte_score']);
        $this->assertEqualsWithDelta(93.0, $summary['final_grade'], 0.001);
    }

    public function test_student_feedback_does_not_count_toward_final(): void
    {
        $student = $this->makeStudent();

        Evaluation::query()->create([
            'student_id' => $student->id,
            'company_id' => null,
            'evaluator_id' => null,
            'evaluation_type' => 'student_feedback',
            'score' => 100,
            'comments' => null,
            'evaluated_at' => now(),
        ]);

        $summary = OjtGradeCalculator::summary($student);
        $this->assertFalse($summary['is_complete']);
    }
}
