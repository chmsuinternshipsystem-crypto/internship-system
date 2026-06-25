<?php

namespace Tests\Feature;

use App\Mail\HteEvaluationLinkMail;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\HteTransactionLink;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class HteLinkDispatchTest extends TestCase
{
    use RefreshDatabase;

    private static ?array $preDocIds = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::$preDocIds = null;
    }

    private function ensurePreDocsApproved(Student $student): void
    {
        if (self::$preDocIds === null) {
            $preDocNames = [
                'Pledge of Good Conduct', 'Memorandum of Agreement', 'Internship Agreement',
                'Parent Consent Form', 'Application Letter', 'Endorsement Letter',
                'Resume', 'Acceptance Letter',
            ];
            self::$preDocIds = [];
            foreach ($preDocNames as $i => $name) {
                $doc = RequiredDocument::create([
                    'name' => $name,
                    'description' => $name,
                    'is_mandatory' => true,
                    'phase' => 'pre',
                    'order_index' => $i + 1,
                ]);
                self::$preDocIds[] = $doc->id;
            }
        }
        foreach (self::$preDocIds as $docId) {
            StudentDocument::create([
                'student_id' => $student->id,
                'required_document_id' => $docId,
                'file_path' => 'test/placeholder.pdf',
                'status' => 'Submitted',
                'workflow_status' => 'completed',
            ]);
        }
    }

    public function test_instructor_can_generate_and_email_hte_link(): void
    {
        Mail::fake();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'last_name' => 'Dispatch',
            'first_name' => 'Student',
            'student_number' => '20238801',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
        $this->ensurePreDocsApproved($student);
        $company = Company::query()->create([
            'name' => 'Dispatch Company',
            'address' => 'Talisay',
            'street_address' => 'Street',
            'barangay' => 'Brgy',
            'city_municipality' => 'Talisay',
            'contact_person' => 'Contact, Person',
            'contact_last_name' => 'Contact',
            'contact_first_name' => 'Person',
            'contact_email' => 'contact@dispatch.test',
            'contact_phone' => '1234567890',
            'is_active' => true,
        ]);
        Deployment::query()->create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($instructor)->post(route('evaluations.hte-links.store'), [
            'student_id' => $student->id,
            'supervisor_name' => 'HTE Supervisor',
            'supervisor_email' => 'hte.supervisor@example.test',
            'expires_in_days' => 5,
        ]);

        $response->assertRedirect(route('evaluations.hte-links.create'));
        $this->assertDatabaseHas('hte_transaction_links', [
            'student_id' => $student->id,
            'company_id' => $company->id,
            'created_by' => $instructor->id,
            'supervisor_name' => 'HTE Supervisor',
            'supervisor_email' => 'hte.supervisor@example.test',
            'used_at' => null,
        ]);
        $link = HteTransactionLink::query()->latest('id')->firstOrFail();
        $this->assertNotEmpty($link->token);

        Mail::assertSent(HteEvaluationLinkMail::class, 1);
        Mail::assertSent(HteEvaluationLinkMail::class, function (HteEvaluationLinkMail $mail): bool {
            return $mail->hasTo('hte.supervisor@example.test');
        });
    }

    public function test_instructor_cannot_send_hte_link_when_student_has_no_active_deployment(): void
    {
        Mail::fake();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = Student::query()->create([
            'last_name' => 'No Deploy',
            'first_name' => 'Student',
            'student_number' => '20238802',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);

        $response = $this->actingAs($instructor)->post(route('evaluations.hte-links.store'), [
            'student_id' => $student->id,
            'supervisor_email' => 'hte.no-deploy@example.test',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('hte_transaction_links', 0);
        Mail::assertNothingSent();
    }

    public function test_send_hte_link_page_lists_all_deployed_students(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $match = Student::query()->create([
            'last_name' => 'Alpha',
            'first_name' => 'Unique',
            'student_number' => '20238810',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
        $this->ensurePreDocsApproved($match);
        $other = Student::query()->create([
            'last_name' => 'Beta',
            'first_name' => 'Other',
            'student_number' => '20238811',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'B',
            'contact_number' => '09123456789',
        ]);
        $this->ensurePreDocsApproved($other);
        $company = Company::query()->create([
            'name' => 'Search Co',
            'address' => 'X',
            'street_address' => 'S',
            'barangay' => 'B',
            'city_municipality' => 'C',
            'contact_person' => 'X, Y',
            'contact_last_name' => 'X',
            'contact_first_name' => 'Y',
            'contact_email' => 'co@search.test',
            'contact_phone' => '1',
            'is_active' => true,
        ]);
        foreach ([$match, $other] as $s) {
            Deployment::query()->create([
                'student_id' => $s->id,
                'company_id' => $company->id,
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'status' => 'active',
            ]);
        }

        $html = $this->actingAs($instructor)
            ->get(route('evaluations.hte-links.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Alpha, Unique', $html);
        $this->assertStringContainsString('Beta, Other', $html);
    }
}
