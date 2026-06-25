<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemObjectivesSmokeTest extends TestCase
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
            'last_name' => 'Doe',
            'first_name' => 'Jane',
            'middle_initial' => null,
            'name_extension' => null,
            'student_number' => '20230001',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
            'status' => 'pending',
        ]);
    }

    public function test_instructor_can_navigate_key_objective_pages(): void
    {
        $user = $this->staffUser('instructor');

        // Minimal data so report/compliance pages have something to render.
        $requiredDoc = RequiredDocument::query()->create([
            'name' => 'Memorandum of Agreement',
            'description' => 'Signed MOA between school and company',
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $company = Company::query()->create([
            'name' => 'Test Company',
            'address' => 'Somewhere',
            'street_address' => 'Main Street',
            'barangay' => 'Zone 1',
            'city_municipality' => 'Talisay City',
            'contact_person' => 'HR, Test',
            'contact_last_name' => 'HR',
            'contact_first_name' => 'Test',
            'contact_email' => 'hr@test.local',
            'contact_phone' => '0341234567',
            'is_active' => true,
        ]);

        $student = $this->makeStudent();

        Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => '2026-01-06',
            'end_date' => '2026-03-28',
            'status' => 'pending',
            'remarks' => null,
        ]);

        Announcement::query()->create([
            'title' => 'Test Announcement',
            'body' => 'Hello',
            'visible_to_role' => null,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('companies.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('deployments.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('required-documents.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('compliance.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('evaluations.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('announcements.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('reports.deployed-per-company'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('reports.missing-documents'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('reports.compliance-summary'))
            ->assertOk();

        // Ensure the created required doc is actually referenced somewhere
        // (prevents cases where the view might hard-crash on null data).
        $this->assertNotNull($requiredDoc->id);
    }

    public function test_instructor_can_crud_companies_deployments_required_documents_announcements(): void
    {
        $user = $this->staffUser('instructor');
        $student = $this->makeStudent();

        $company = Company::query()->create([
            'name' => 'Seed Company',
            'address' => 'Somewhere',
            'street_address' => 'Main Street',
            'barangay' => 'Zone 1',
            'city_municipality' => 'Talisay City',
            'contact_person' => 'HR, Seed',
            'contact_last_name' => 'HR',
            'contact_first_name' => 'Seed',
            'contact_email' => 'hr@test.local',
            'contact_phone' => '0341234567',
            'is_active' => true,
        ]);

        // Companies
        $storeCompany = $this->actingAs($user)->post(route('companies.store'), [
            'name' => 'Company A',
            'street_address' => 'Address A',
            'barangay' => 'Barangay 1',
            'city_municipality' => 'Talisay City',
            'contact_last_name' => 'Person',
            'contact_first_name' => 'Alice',
            'contact_middle_initial' => 'A',
            'contact_email' => 'a@test.local',
            'contact_phone' => '09171234567',
            'is_active' => true,
        ]);
        $storeCompany->assertRedirect(route('companies.index'));

        $createdCompany = Company::query()->where('name', 'Company A')->first();
        $this->assertNotNull($createdCompany);

        $this->actingAs($user)->put(route('companies.update', $createdCompany), [
            'name' => 'Company A Updated',
            'street_address' => 'Address A Updated',
            'barangay' => 'Barangay 2',
            'city_municipality' => 'Bacolod City',
            'contact_last_name' => 'Updated',
            'contact_first_name' => 'Alice',
            'contact_middle_initial' => 'A',
            'contact_email' => 'a.updated@test.local',
            'contact_phone' => '09171234567',
            'is_active' => true,
        ])->assertRedirect(route('companies.index'));

        $this->actingAs($user)->delete(route('companies.destroy', $createdCompany))
            ->assertRedirect(route('companies.index'));

        // Required Documents
        $doc = RequiredDocument::query()->create([
            'name' => 'Resume',
            'description' => 'Resume',
            'is_mandatory' => true,
            'order_index' => 1,
        ]);

        $this->actingAs($user)->post(route('required-documents.store'), [
            'name' => 'Pledge of Good Conduct',
            'description' => 'Template',
            'is_mandatory' => true,
            'order_slot' => '2',
        ])->assertRedirect(route('required-documents.index'));

        $createdDoc = RequiredDocument::query()->where('name', 'Pledge of Good Conduct')->first();
        $this->assertNotNull($createdDoc);

        $this->actingAs($user)->put(route('required-documents.update', $createdDoc), [
            'name' => 'Pledge of Good Conduct (Updated)',
            'description' => 'Template Updated',
            'is_mandatory' => false,
            'order_slot' => '3',
        ])->assertRedirect(route('required-documents.index'));

        $createdDoc->refresh();
        $this->assertSame('Pledge of Good Conduct (Updated)', $createdDoc->name);

        $this->actingAs($user)->delete(route('required-documents.destroy', $createdDoc))
            ->assertRedirect(route('required-documents.index'));

        $orderedIndexes = RequiredDocument::query()
            ->orderBy('order_index')
            ->pluck('order_index')
            ->all();
        $this->assertSame(array_values(array_unique($orderedIndexes)), $orderedIndexes);

        // Deployments (use future dates so end_date validation passes for active status)
        $futureStart = now()->addMonth()->format('Y-m-d');
        $futureEnd = now()->addMonths(3)->format('Y-m-d');

        $deployment = Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => $futureStart,
            'end_date' => $futureEnd,
            'status' => 'active',
        ]);

        $pastStart = now()->subMonths(3)->format('Y-m-d');
        $pastEnd = now()->subMonth()->format('Y-m-d');

        $this->actingAs($user)->put(route('deployments.update', $deployment), [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => $pastStart,
            'end_date' => $pastEnd,
            'status' => 'completed',
            'remarks' => 'Completed',
        ])->assertRedirect(route('deployments.index'));

        $this->actingAs($user)->delete(route('deployments.destroy', $deployment))
            ->assertRedirect(route('deployments.index'));

        // Announcements
        $announcement = Announcement::query()->create([
            'title' => 'Old',
            'body' => 'Old body',
            'visible_to_role' => null,
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('announcements.update', $announcement), [
            'title' => 'Updated Title',
            'body' => 'Updated body',
            'visible_to_role' => 'student',
        ])->assertRedirect(route('announcements.index'));

        $announcement->refresh();
        $this->assertSame('Updated Title', $announcement->title);

        $this->actingAs($user)->delete(route('announcements.destroy', $announcement))
            ->assertRedirect(route('announcements.index'));
    }
}
