<?php

namespace Tests\Feature;

use App\Models\HteTransactionLink;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HteTransactionLinkTest extends TestCase
{
    use RefreshDatabase;

    private function makeLink(): HteTransactionLink
    {
        $student = Student::query()->create([
            'name' => 'HTE Student',
            'student_number' => '20231111',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);

        StudentAccount::query()->create([
            'student_id' => $student->id,
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        return HteTransactionLink::query()->create([
            'token' => 'tokentest123',
            'student_id' => $student->id,
            'company_id' => null,
            'expires_at' => now()->addHour(),
        ]);
    }

    public function test_hte_can_submit_evaluation_without_login(): void
    {
        $link = $this->makeLink();

        $response = $this->post(route('hte.transaction.evaluate', $link->token), [
            'criteria_scores' => [
                'work_habits' => ['punctual' => 4, 'reports_regularly' => 4, 'perform_without_supervision' => 4, 'self_discipline' => 4, 'dedication' => 4],
                'work_skills' => ['operate_machines' => 4, 'handles_details' => 4, 'flexibility' => 4, 'thoroughness' => 4, 'understands_linkage' => 4, 'sound_suggestions' => 4],
                'social_skills' => ['tact' => 4, 'respect_courtesy' => 4, 'willingly_helps' => 4, 'learns_from_others' => 4, 'gratitude' => 4, 'poise_grooming' => 4, 'emotional_maturity' => 4],
            ],
            'comments' => 'Good attendance and punctuality.',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('evaluations', [
            'student_id' => $link->student_id,
            'evaluation_type' => 'industry',
            'evaluator_id' => null,
        ]);
        $eval = \App\Models\Evaluation::where('student_id', $link->student_id)->latest()->first();
        $this->assertNotNull($eval);
        $this->assertEquals(80, $eval->score); // 4.0 avg * 20
        $this->assertDatabaseHas('hte_transaction_links', [
            'id' => $link->id,
            'used_for' => 'evaluation',
        ]);
    }

    public function test_hte_can_upload_document_without_login(): void
    {
        Storage::fake('public');
        $link = $this->makeLink();
        $requiredDocument = RequiredDocument::query()->create([
            'name' => 'HTE Uploaded Form',
            'is_mandatory' => false,
            'order_index' => 1,
        ]);

        $response = $this->post(route('hte.transaction.upload', $link->token), [
            'required_document_id' => $requiredDocument->id,
            'file' => UploadedFile::fake()->create('hte-form.pdf', 120, 'application/pdf'),
            'supervisor_name' => 'HTE Supervisor',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $link->student_id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Submitted',
            'uploaded_by' => null,
        ]);
        $this->assertDatabaseHas('hte_transaction_links', [
            'id' => $link->id,
            'used_for' => 'document_upload',
        ]);
    }
}
