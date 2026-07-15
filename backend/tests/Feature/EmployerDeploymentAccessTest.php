<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerDeploymentAccessTest extends TestCase
{
    use RefreshDatabase;

    private function seedDeployment(): array
    {
        $student = Student::query()->create([
            'user_id' => null,
            'name' => 'Deploy Student',
            'student_number' => '20230111',
            'program' => 'BSIS',
            'year_level' => 3,
            'section' => 'A',
            'contact_number' => '09123456789',
            'status' => 'pending',
        ]);
        $company = Company::query()->create([
            'name' => 'Sample Co',
            'street_address' => 'Main Street',
            'barangay' => 'Zone 1',
            'city_municipality' => 'Talisay City',
            'contact_last_name' => 'Santos',
            'contact_first_name' => 'Mia',
            'contact_email' => null,
            'contact_phone' => null,
            'is_active' => true,
        ]);
        $deployment = Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => '2026-02-01',
            'end_date' => null,
            'status' => 'pending',
            'remarks' => null,
        ]);

        return compact('student', 'company', 'deployment');
    }

    public function test_chairperson_cannot_access_deployment_write_actions(): void
    {
        $chairperson = User::factory()->create(['role' => 'chairperson']);
        $data = $this->seedDeployment();

        $this->actingAs($chairperson)->get(route('deployments.index'))->assertOk();
        $this->actingAs($chairperson)->get(route('deployments.show', $data['deployment']))->assertOk();
    }

    public function test_dean_can_view_deployment_index_and_show(): void
    {
        $dean = User::factory()->create(['role' => 'dean']);
        $data = $this->seedDeployment();

        $this->actingAs($dean)->get(route('deployments.index'))->assertOk();
        $this->actingAs($dean)->get(route('deployments.show', $data['deployment']))->assertOk();
    }

    public function test_dean_and_chairperson_can_open_workflow_queue_for_pending_documents(): void
    {
        $dean = User::factory()->create(['role' => 'dean']);
        $chairperson = User::factory()->create(['role' => 'chairperson']);

        $this->actingAs($dean)->get(route('student-documents.queue'))->assertOk();
        $this->actingAs($chairperson)->get(route('student-documents.queue'))->assertOk();
    }

    public function test_required_documents_catalog_is_instructor_only(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $dean = User::factory()->create(['role' => 'dean']);
        $chairperson = User::factory()->create(['role' => 'chairperson']);

        $this->actingAs($instructor)->get(route('required-documents.index'))->assertOk();
        $this->actingAs($dean)->get(route('required-documents.index'))->assertForbidden();
        $this->actingAs($chairperson)->get(route('required-documents.index'))->assertForbidden();
    }

    public function test_instructor_can_access_institutional_and_catalog_routes(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $data = $this->seedDeployment();

        $this->actingAs($instructor)->get(route('students.index'))->assertOk();
        $this->actingAs($instructor)->get(route('students.show', $data['student']))->assertOk();
        $this->actingAs($instructor)->get(route('compliance.index'))->assertOk();
        $this->actingAs($instructor)->get(route('evaluations.index'))->assertOk();
        $this->actingAs($instructor)->get(route('attendance.index'))->assertOk();
        $this->actingAs($instructor)->get(route('reports.index'))->assertOk();
        $this->actingAs($instructor)->get(route('required-documents.index'))->assertOk();
    }

    public function test_dean_can_access_read_only_modules(): void
    {
        $dean = User::factory()->create(['role' => 'dean']);

        $this->actingAs($dean)->get(route('companies.index'))->assertOk();
        $this->actingAs($dean)->get(route('student-documents.queue'))->assertOk();
        $this->actingAs($dean)->get(route('messages.index'))->assertOk();
        $this->actingAs($dean)->get(route('announcements.index'))->assertOk();
        $this->actingAs($dean)->get(route('required-documents.index'))->assertForbidden();
    }
}
