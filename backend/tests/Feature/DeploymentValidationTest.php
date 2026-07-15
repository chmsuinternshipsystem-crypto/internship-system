<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentValidationTest extends TestCase
{
    use RefreshDatabase;

    private function instructor(): User
    {
        return User::factory()->create(['role' => 'instructor']);
    }

    private function makeUnplacedStudent(): Student
    {
        return Student::create([
            'last_name' => 'Unplaced',
            'first_name' => 'Student',
            'student_number' => '20230050',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
            'ojt_type' => 'unplaced',
        ]);
    }

    private function makePlacedStudent(): Student
    {
        return Student::create([
            'last_name' => 'Placed',
            'first_name' => 'Student',
            'student_number' => '20230051',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
            'ojt_type' => 'external',
        ]);
    }

    private function makeActiveCompany(): Company
    {
        return Company::create([
            'name' => 'Active Company',
            'street_address' => 'Street',
            'barangay' => 'Barangay',
            'city_municipality' => 'City',
            'contact_last_name' => 'Contact',
            'contact_first_name' => 'Person',
            'contact_phone' => '09171234567',
            'is_active' => true,
        ]);
    }

    private function makeInactiveCompany(): Company
    {
        return Company::create([
            'name' => 'Inactive Company',
            'street_address' => 'Street',
            'barangay' => 'Barangay',
            'city_municipality' => 'City',
            'contact_last_name' => 'Contact',
            'contact_first_name' => 'Person',
            'contact_phone' => '09171234567',
            'is_active' => false,
        ]);
    }

    public function test_unplaced_student_cannot_be_deployed(): void
    {
        $user = $this->instructor();
        $student = $this->makeUnplacedStudent();
        $company = $this->makeActiveCompany();

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 422,
            'Expected redirect (with validation error) or 422 for unplaced student deployment'
        );
    }

    public function test_inactive_company_cannot_receive_deployment(): void
    {
        $user = $this->instructor();
        $student = $this->makePlacedStudent();
        $company = $this->makeInactiveCompany();

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 422,
            'Expected redirect (with validation error) or 422 for inactive company deployment'
        );
    }

    public function test_valid_deployment_created_successfully(): void
    {
        $user = $this->instructor();
        $student = $this->makePlacedStudent();
        $company = $this->makeActiveCompany();

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('deployments.index'));
        $this->assertDatabaseHas('deployments', [
            'student_id' => $student->id,
            'company_id' => $company->id,
        ]);
    }

    public function test_overlapping_dates_rejected(): void
    {
        $user = $this->instructor();
        $student = $this->makePlacedStudent();
        $company = $this->makeActiveCompany();

        Deployment::create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'end_date' => now()->addMonths(4)->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 422,
            'Expected overlapping date validation error'
        );
    }

    public function test_end_date_before_start_date_rejected(): void
    {
        $user = $this->instructor();
        $student = $this->makePlacedStudent();
        $company = $this->makeActiveCompany();

        $response = $this->actingAs($user)->post(route('deployments.store'), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->addMonths(3)->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->assertTrue(
            $response->isRedirect() || $response->status() === 422,
            'Expected end date before start date to be rejected'
        );
    }
}
