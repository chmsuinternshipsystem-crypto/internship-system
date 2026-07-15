<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_active_deployment_sets_student_to_deployed(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'user_id' => null,
            'last_name' => 'Sync',
            'first_name' => 'Test',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230003',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
        $company = Company::query()->create([
            'name' => 'ACME Corp',
            'street_address' => 'Main Street',
            'barangay' => 'Zone 1',
            'city_municipality' => 'Talisay City',
            'contact_last_name' => 'Cruz',
            'contact_first_name' => 'Ana',
            'contact_email' => null,
            'contact_phone' => null,
            'is_active' => true,
        ]);

        $requiredDoc = RequiredDocument::query()->create([
            'name' => 'Test Document',
            'is_mandatory' => true,
            'phase' => 'pre',
        ]);
        StudentDocument::query()->create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'file_path' => 'test.pdf',
        ]);

        \App\Models\Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => '2026-01-15',
            'status' => 'active',
        ]);

        $student->refresh();
        $this->assertTrue($student->isDeploymentEligibleForPortal());
    }

    public function test_deleting_last_deployment_resets_student_to_pending(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'user_id' => null,
            'last_name' => 'Sync',
            'first_name' => 'Test 2',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230004',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
        $company = Company::query()->create([
            'name' => 'ACME Two',
            'street_address' => 'Second Street',
            'barangay' => 'Zone 2',
            'city_municipality' => 'Bacolod City',
            'contact_last_name' => 'Reyes',
            'contact_first_name' => 'Ben',
            'contact_email' => null,
            'contact_phone' => null,
            'is_active' => true,
        ]);

        $requiredDoc = RequiredDocument::query()->create([
            'name' => 'Test Document 2',
            'is_mandatory' => true,
            'phase' => 'pre',
        ]);
        StudentDocument::query()->create([
            'student_id' => $student->id,
            'required_document_id' => $requiredDoc->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'file_path' => 'test.pdf',
        ]);

        $deployment = Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => '2026-02-01',
            'end_date' => null,
            'status' => 'active',
            'remarks' => null,
        ]);

        $this->assertTrue($student->fresh()->isDeploymentEligibleForPortal());

        $this->actingAs($user)->delete(route('deployments.destroy', $deployment))
            ->assertRedirect(route('deployments.index'));

        $student->refresh();
        $this->assertFalse($student->isDeploymentEligibleForPortal());
    }
}
