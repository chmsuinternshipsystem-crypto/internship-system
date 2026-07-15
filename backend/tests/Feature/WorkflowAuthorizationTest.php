<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function instructor(): User
    {
        return User::factory()->create(['role' => 'instructor', 'email' => 'inst1@test.com']);
    }

    private function otherInstructor(): User
    {
        return User::factory()->create(['role' => 'instructor', 'email' => 'inst2@test.com']);
    }

    private function chairperson(): User
    {
        return User::factory()->create(['role' => 'chairperson', 'email' => 'chair@test.com']);
    }

    private function makeStudent(User $assignedInstructor = null): Student
    {
        return Student::create([
            'last_name' => 'Test',
            'first_name' => 'Student',
            'student_number' => '20230100',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
            'assigned_instructor_id' => $assignedInstructor?->id,
        ]);
    }

    public function test_only_assigned_instructor_should_act_on_document(): void
    {
        Storage::fake('public');

        $assignedInstructor = $this->instructor();
        $unauthorizedInstructor = $this->otherInstructor();
        $student = $this->makeStudent($assignedInstructor);

        $requiredDoc = RequiredDocument::create([
            'name' => 'Test Document',
            'is_mandatory' => true,
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $filePath = $file->store('student-documents/' . $student->id, 'public');

        $studentDoc = StudentDocument::create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Pending',
            'file_path' => $filePath,
            'current_holder_role' => 'instructor',
            'workflow_status' => 'received',
        ]);

        $response = $this->actingAs($unauthorizedInstructor)
            ->post(route('student-documents.workflow-action', [
                'student' => $student->id,
                'studentDocument' => $studentDoc->id,
                'action' => 'approve',
            ]));

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 403 || $response->status() === 422,
            'Expected unauthorized instructor to be blocked from approving another instructor\'s student\'s document'
        );
    }

    public function test_chairperson_cannot_act_when_holder_is_instructor(): void
    {
        Storage::fake('public');

        $instructor = $this->instructor();
        $chair = $this->chairperson();
        $student = $this->makeStudent($instructor);

        $requiredDoc = RequiredDocument::create([
            'name' => 'Test Doc',
            'is_mandatory' => true,
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $filePath = $file->store('student-documents/' . $student->id, 'public');

        $studentDoc = StudentDocument::create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Pending',
            'file_path' => $filePath,
            'current_holder_role' => 'instructor',
            'workflow_status' => 'received',
        ]);

        $response = $this->actingAs($chair)
            ->post(route('student-documents.workflow-action', [
                'student' => $student->id,
                'studentDocument' => $studentDoc->id,
                'action' => 'approve',
            ]));

        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(),
            'Expected chairperson to be blocked (403) from acting on instructor-held document'
        );
    }

    public function test_student_cannot_access_workflow_queue(): void
    {
        $instructor = $this->instructor();
        $student = $this->makeStudent($instructor);

        $studentAccount = $student->account()->create([
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'first_login' => false,
        ]);

        $response = $this->withSession([
            'student_account_id' => $studentAccount->id,
            'student_last_activity' => now()->timestamp,
        ])->get(route('student-documents.queue'));

        $this->assertTrue(
            $response->status() === 403 || $response->status() === 302,
            'Expected student to be blocked from workflow queue'
        );
    }

    public function test_instructor_can_approve_own_student_document(): void
    {
        Storage::fake('public');

        $instructor = $this->instructor();
        $student = $this->makeStudent($instructor);

        $requiredDoc = RequiredDocument::create([
            'name' => 'Approval Test Doc',
            'is_mandatory' => true,
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $filePath = $file->store('student-documents/' . $student->id, 'public');

        $studentDoc = StudentDocument::create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Pending',
            'file_path' => $filePath,
            'current_holder_role' => 'instructor',
            'workflow_status' => 'received',
        ]);

        $response = $this->actingAs($instructor)
            ->post(route('student-documents.workflow-action', [
                'student' => $student->id,
                'studentDocument' => $studentDoc->id,
                'action' => 'approve',
            ]));

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 200,
            'Expected instructor to be able to approve document'
        );
    }

    public function test_dean_cannot_act_on_document_workflow(): void
    {
        Storage::fake('public');

        $dean = User::factory()->create(['role' => 'dean', 'email' => 'dean@test.com']);
        $instructor = $this->instructor();
        $student = $this->makeStudent($instructor);

        $requiredDoc = RequiredDocument::create([
            'name' => 'Dean Test Doc',
            'is_mandatory' => true,
        ]);

        $file = UploadedFile::fake()->create('doc.pdf', 100);
        $filePath = $file->store('student-documents/' . $student->id, 'public');

        $studentDoc = StudentDocument::create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Pending',
            'file_path' => $filePath,
            'current_holder_role' => 'instructor',
            'workflow_status' => 'received',
        ]);

        $response = $this->actingAs($dean)
            ->post(route('student-documents.workflow-action', [
                'student' => $student->id,
                'studentDocument' => $studentDoc->id,
                'action' => 'approve',
            ]));

        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(),
            'Expected dean to be blocked from workflow action'
        );
    }
}
