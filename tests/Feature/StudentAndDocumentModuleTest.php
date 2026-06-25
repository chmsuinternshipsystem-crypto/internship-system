<?php

namespace Tests\Feature;

use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAndDocumentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function staffUser(string $role = 'instructor'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function makeStudent(): Student
    {
        return Student::query()->create([
            'user_id' => null,
            'last_name' => 'Student',
            'first_name' => 'Test',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230001',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
    }

    public function test_staff_can_view_student_list_and_create_form(): void
    {
        $user = $this->staffUser();

        $this->actingAs($user)
            ->get(route('students.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('students.create'))
            ->assertOk();
    }

    public function test_staff_can_create_student(): void
    {
        $user = $this->staffUser();

        $response = $this->actingAs($user)->post(route('students.store'), [
            'last_name' => 'Student',
            'first_name' => 'New',
            'middle_name' => 'N',
            'name_extension' => 'Jr.',
            'account_password' => 'password123',
            'account_password_confirmation' => 'password123',
            'student_number' => '20230002',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'B',
            'contact_number' => '09987654321',
        ]);

        $response->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', [
            'student_number' => '20230002',
            'last_name' => 'Student',
            'first_name' => 'New',
            'name' => 'Student, New N Jr.',
        ]);
        $this->assertDatabaseHas('student_accounts', [
            'student_id' => Student::query()->where('student_number', '20230002')->value('id'),
            'email' => null,
        ]);
    }

    public function test_student_index_search_matches_section(): void
    {
        $user = $this->staffUser();
        $this->makeStudent();
        Student::query()->create([
            'user_id' => null,
            'last_name' => 'Gamma',
            'first_name' => 'Zoe',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20239999',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'C',
            'contact_number' => '09111111111',
        ]);

        $html = $this->actingAs($user)
            ->get(route('students.index', ['search' => 'C']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('20239999', $html);
        $this->assertStringNotContainsString('20230001', $html);
    }

    public function test_staff_can_view_student_documents_edit_and_update_valid_keys(): void
    {
        $user = $this->staffUser();
        $student = $this->makeStudent();
        $doc = RequiredDocument::query()->create([
            'name' => 'Resume',
            'description' => null,
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('student-documents.edit', $student))
            ->assertOk();

        $response = $this->actingAs($user)->post(
            route('student-documents.update', $student),
            [
                'documents' => [
                    (string) $doc->id => [
                        'status' => 'Missing',
                        'submitted_at' => null,
                    ],
                ],
            ]
        );

        $response->assertRedirect(route('student-documents.edit', $student));
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $student->id,
            'required_document_id' => $doc->id,
            'status' => 'Missing',
        ]);
    }

    public function test_student_documents_update_rejects_invalid_document_keys(): void
    {
        $user = $this->staffUser();
        $student = $this->makeStudent();
        RequiredDocument::query()->create([
            'name' => 'Valid Doc',
            'description' => null,
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $response = $this->actingAs($user)->post(
            route('student-documents.update', $student),
            [
                'documents' => [
                    '99999' => [
                        'status' => 'Missing',
                        'submitted_at' => null,
                    ],
                ],
            ]
        );

        $response->assertSessionHasErrors('documents');
    }

    public function test_chairperson_cannot_post_student_document_updates(): void
    {
        $user = $this->staffUser('chairperson');
        $student = $this->makeStudent();
        $doc = RequiredDocument::query()->create([
            'name' => 'Form 1',
            'description' => null,
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('student-documents.edit', $student))
            ->assertOk();

        $response = $this->actingAs($user)->post(
            route('student-documents.update', $student),
            [
                'documents' => [
                    (string) $doc->id => [
                        'status' => 'Submitted',
                        'submitted_at' => now()->toDateString(),
                    ],
                ],
            ]
        );

        $response->assertForbidden();
    }

    public function test_dean_cannot_post_student_document_updates(): void
    {
        $user = $this->staffUser('dean');
        $student = $this->makeStudent();
        $doc = RequiredDocument::query()->create([
            'name' => 'Form 1',
            'description' => null,
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('student-documents.edit', $student))
            ->assertOk();

        $response = $this->actingAs($user)->post(
            route('student-documents.update', $student),
            [
                'documents' => [
                    (string) $doc->id => [
                        'status' => 'Submitted',
                        'submitted_at' => now()->toDateString(),
                    ],
                ],
            ]
        );

        $response->assertForbidden();
    }

    public function test_dean_can_open_student_documents_checklist_read_only(): void
    {
        $user = $this->staffUser('dean');
        $student = $this->makeStudent();
        RequiredDocument::query()->create([
            'name' => 'Form 1',
            'description' => null,
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('student-documents.edit', $student))
            ->assertOk();
    }
}
