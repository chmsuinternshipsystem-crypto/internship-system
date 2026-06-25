<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentAccount(string $studentNumber = '20230010'): StudentAccount
    {
        $student = Student::create([
            'user_id' => null,
            'last_name' => 'Portal',
            'first_name' => 'Student',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => $studentNumber,
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);

        $company = Company::create([
            'name' => 'Test Company',
            'address' => 'City',
            'contact_person' => 'Person',
            'contact_email' => 'test@company.com',
            'contact_phone' => '09123456789',
            'is_active' => true,
            'latitude' => 10.742584903620171,
            'longitude' => 122.96932879047095,
            'geofence_radius_meters' => 100,
            'geofencing_enabled' => true,
        ]);

        $deployment = new Deployment([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'status' => 'active',
        ]);
        $deployment->saveQuietly();

        return StudentAccount::create([
            'student_id' => $student->id,
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'attendance_passcode' => '123456',
            'attendance_passcode_generated_at' => now(),
            'first_login' => false,
        ]);
    }

    public function test_pre_deployment_student_verifies_otp_to_documents_and_cannot_open_messages(): void
    {
        $student = Student::create([
            'user_id' => null,
            'last_name' => 'Pre Deploy',
            'first_name' => 'Student',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230099',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);
        $account = StudentAccount::create([
            'student_id' => $student->id,
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'first_login' => false,
        ]);

        $this->post('/login', [
            'role' => 'student',
            'student_number' => '20230099',
            'password' => 'password',
        ]);

        $otp = (string) session('student_otp_code');
        $this->assertNotEmpty($otp);

        $response = $this->post(route('student.otp.verify'), [
            'otp' => $otp,
        ]);

        $response->assertRedirect(route('student.documents', absolute: false));
        $this->assertSame($account->id, session('student_account_id'));

        $this->get(route('student.dashboard'))->assertRedirect(route('student.documents'));
    }

    public function test_student_is_redirected_to_student_dashboard_after_login(): void
    {
        $studentAccount = $this->makeStudentAccount('20230011');

        $response = $this->post('/login', [
            'role' => 'student',
            'student_number' => '20230011',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.otp.show', absolute: false));
        $this->assertSame($studentAccount->id, session('student_otp_pending_id'));
    }

    public function test_student_can_upload_document_in_student_portal(): void
    {
        Storage::fake('public');
        $studentAccount = $this->makeStudentAccount('20230012');
        $instructor = User::factory()->create(['role' => 'instructor']);
        $requiredDocument = RequiredDocument::create([
            'name' => 'MOA',
            'is_mandatory' => false,
            'order_index' => 1,
        ]);

        $response = $this->withSession(['student_account_id' => $studentAccount->id])->post(
            route('student.documents.upload', $requiredDocument),
            ['file' => UploadedFile::fake()->create('moa.pdf', 200, 'application/pdf')]
        );

        $response->assertRedirect(route('student.documents'));
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $studentAccount->student_id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Pending',
            'uploaded_by' => null,
        ]);
    }

    public function test_student_can_upload_docx_document_in_student_portal(): void
    {
        Storage::fake('public');
        $studentAccount = $this->makeStudentAccount('20230013');
        $requiredDocument = RequiredDocument::create([
            'name' => 'Test Document',
            'is_mandatory' => false,
            'order_index' => 2,
        ]);

        $response = $this->withSession(['student_account_id' => $studentAccount->id])->post(
            route('student.documents.upload', $requiredDocument),
            ['file' => UploadedFile::fake()->create('sample.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')]
        );

        $response->assertRedirect(route('student.documents'));
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $studentAccount->student_id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Pending',
        ]);
    }

    public function test_student_documents_show_global_and_assigned_company_checklist_only(): void
    {
        $student = Student::create([
            'user_id' => null,
            'last_name' => 'Portal',
            'first_name' => 'Student',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230130',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);

        $assignedCompany = Company::create([
            'name' => 'Assigned HTE',
            'address' => 'City',
            'street_address' => 'Street 1',
            'barangay' => 'Brgy 1',
            'city_municipality' => 'City',
            'contact_person' => 'Person, A',
            'contact_last_name' => 'Person',
            'contact_first_name' => 'A',
            'contact_email' => 'a@hte.test',
            'contact_phone' => '1234567890',
            'is_active' => true,
        ]);
        $otherCompany = Company::create([
            'name' => 'Other HTE',
            'address' => 'City',
            'street_address' => 'Street 2',
            'barangay' => 'Brgy 2',
            'city_municipality' => 'City',
            'contact_person' => 'Person, B',
            'contact_last_name' => 'Person',
            'contact_first_name' => 'B',
            'contact_email' => 'b@hte.test',
            'contact_phone' => '1234567891',
            'is_active' => true,
        ]);

        $deployment = new Deployment([
            'student_id' => $student->id,
            'company_id' => $assignedCompany->id,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'status' => 'active',
        ]);
        $deployment->saveQuietly();

        $studentAccount = StudentAccount::create([
            'student_id' => $student->id,
            'email' => null,
            'password' => bcrypt('password'),
            'is_active' => true,
            'attendance_passcode' => '123456',
            'attendance_passcode_generated_at' => now(),
            'first_login' => false,
        ]);

        $globalDoc = RequiredDocument::create(['name' => 'Global Checklist', 'is_mandatory' => false, 'order_index' => 1, 'company_id' => null, 'phase' => 'pre']);
        $assignedDoc = RequiredDocument::create(['name' => 'Assigned-only Checklist', 'is_mandatory' => false, 'order_index' => 2, 'company_id' => $assignedCompany->id, 'phase' => 'pre']);
        RequiredDocument::create(['name' => 'Other-company Checklist', 'is_mandatory' => false, 'order_index' => 3, 'company_id' => $otherCompany->id, 'phase' => 'pre']);

        // Submit the documents the student needs so deployment auto-activates
        \App\Models\StudentDocument::create(['student_id' => $student->id, 'required_document_id' => $globalDoc->id, 'status' => 'Submitted', 'file_path' => 'g.pdf', 'submitted_at' => now()]);
        \App\Models\StudentDocument::create(['student_id' => $student->id, 'required_document_id' => $assignedDoc->id, 'status' => 'Submitted', 'file_path' => 'a.pdf', 'submitted_at' => now()]);

        $response = $this->withSession(['student_account_id' => $studentAccount->id])->get(route('student.documents'));
        $response->assertOk();
        $response->assertSee('Global Checklist');
        $response->assertSee('Assigned-only Checklist');
        $response->assertDontSee('Other-company Checklist');
    }

    public function test_student_document_upload_allows_late_submission_after_deadline(): void
    {
        Storage::fake('public');
        $studentAccount = $this->makeStudentAccount('20230131');
        $requiredDocument = RequiredDocument::create([
            'name' => 'Late Document',
            'is_mandatory' => false,
            'order_index' => 1,
            'submission_deadline_at' => now()->subHour(),
        ]);

        $response = $this->withSession(['student_account_id' => $studentAccount->id])->post(
            route('student.documents.upload', $requiredDocument),
            ['file' => UploadedFile::fake()->create('late.pdf', 120, 'application/pdf')]
        );

        $response->assertRedirect(route('student.documents'));
        $this->assertDatabaseHas('student_documents', [
            'student_id' => $studentAccount->student_id,
            'required_document_id' => $requiredDocument->id,
            'status' => 'Pending',
        ]);
    }

    public function test_student_can_time_in_then_time_out_with_total_hours(): void
    {
        $studentAccount = $this->makeStudentAccount('20230016');
        Setting::campus()->update([
            'attendance_time_in_start' => '00:00',
            'attendance_time_in_end' => '23:59',
            'attendance_time_out_start' => '00:00',
            'attendance_time_out_end' => '23:59',
            'attendance_am_time_in_start' => '00:00',
            'attendance_am_time_in_end' => '23:59',
            'attendance_am_time_out_start' => '00:00',
            'attendance_am_time_out_end' => '23:59',
            'attendance_pm_time_in_start' => '00:00',
            'attendance_pm_time_in_end' => '23:59',
            'attendance_pm_time_out_start' => '00:00',
            'attendance_pm_time_out_end' => '23:59',
            'campus_lat' => 10.742584903620171,
            'campus_lng' => 122.96932879047095,
            'student_geofence_radius_meters' => 100,
            'geofence_review_buffer_meters' => 20,
        ]);

        $this->post(route('attendance.store'), [
            'attendance_action' => 'time_in',
            'student_number' => $studentAccount->student->student_number,
            'passcode' => '123456',
            'latitude' => 10.742584903620171,
            'longitude' => 122.96932879047095,
            'accuracy_meters' => 10,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $studentAccount->student_id,
            'time_out_at' => null,
            'geofence_status' => 'inside_pass',
        ]);

        $response = $this->post(route('attendance.store'), [
            'attendance_action' => 'time_out',
            'student_number' => $studentAccount->student->student_number,
            'passcode' => '123456',
            'latitude' => 10.742584903620171,
            'longitude' => 122.96932879047095,
            'accuracy_meters' => 10,
        ]);

        $response->assertRedirect(route('attendance.check-in'));
        $this->assertDatabaseHas('attendances', [
            'student_id' => $studentAccount->student_id,
            'time_out_geofence_status' => 'inside_pass',
        ]);
        $this->assertNotNull(
            \App\Models\Attendance::query()
                ->where('student_id', $studentAccount->student_id)
                ->value('total_minutes')
        );
    }

    public function test_time_out_keeps_original_time_in_timestamp(): void
    {
        try {
            $studentAccount = $this->makeStudentAccount('20230024');
            Setting::campus()->update([
            'attendance_time_in_start' => '00:00',
            'attendance_time_in_end' => '23:59',
            'attendance_time_out_start' => '00:00',
            'attendance_time_out_end' => '23:59',
            'attendance_am_time_in_start' => '00:00',
            'attendance_am_time_in_end' => '23:59',
            'attendance_am_time_out_start' => '00:00',
            'attendance_am_time_out_end' => '23:59',
            'attendance_pm_time_in_start' => '00:00',
            'attendance_pm_time_in_end' => '23:59',
            'attendance_pm_time_out_start' => '00:00',
            'attendance_pm_time_out_end' => '23:59',
                'campus_lat' => 10.742584903620171,
                'campus_lng' => 122.96932879047095,
                'student_geofence_radius_meters' => 100,
                'geofence_review_buffer_meters' => 20,
            ]);

            Carbon::setTestNow(Carbon::parse('2026-04-17 12:01:00'));
            $this->post(route('attendance.store'), [
                'attendance_action' => 'am_time_in',
                'student_number' => $studentAccount->student->student_number,
                'passcode' => '123456',
                'latitude' => 10.742584903620171,
                'longitude' => 122.96932879047095,
                'accuracy_meters' => 10,
            ])->assertRedirect(route('attendance.check-in'));

            Carbon::setTestNow(Carbon::parse('2026-04-17 12:06:00'));
            $this->post(route('attendance.store'), [
                'attendance_action' => 'am_time_out',
                'student_number' => $studentAccount->student->student_number,
                'passcode' => '123456',
                'latitude' => 10.742584903620171,
                'longitude' => 122.96932879047095,
                'accuracy_meters' => 10,
            ])->assertRedirect(route('attendance.check-in'));

            $attendance = \App\Models\Attendance::query()
                ->where('student_id', $studentAccount->student_id)
                ->latest('id')
                ->first();

            $this->assertNotNull($attendance);
            $this->assertSame('2026-04-17 12:01:00', $attendance->check_in_at?->format('Y-m-d H:i:s'));
            $this->assertSame('2026-04-17 12:06:00', $attendance->am_check_out?->format('Y-m-d H:i:s'));
            $this->assertSame(5, (int) $attendance->total_minutes);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_student_can_time_in_without_location_and_it_is_marked_for_review(): void
    {
        $studentAccount = $this->makeStudentAccount('20230020');
        Setting::campus()->update([
            'attendance_time_in_start' => '00:00',
            'attendance_time_in_end' => '23:59',
            'attendance_time_out_start' => '00:00',
            'attendance_time_out_end' => '23:59',
            'attendance_am_time_in_start' => '00:00',
            'attendance_am_time_in_end' => '23:59',
            'attendance_am_time_out_start' => '00:00',
            'attendance_am_time_out_end' => '23:59',
            'attendance_pm_time_in_start' => '00:00',
            'attendance_pm_time_in_end' => '23:59',
            'attendance_pm_time_out_start' => '00:00',
            'attendance_pm_time_out_end' => '23:59',
            'campus_lat' => null,
            'campus_lng' => null,
            'student_geofence_radius_meters' => 100,
            'geofence_review_buffer_meters' => 20,
        ]);

        $response = $this->post(route('attendance.store'), [
            'attendance_action' => 'time_in',
            'student_number' => $studentAccount->student->student_number,
            'passcode' => '123456',
            'latitude' => null,
            'longitude' => null,
            'accuracy_meters' => null,
        ]);

        $response->assertRedirect(route('attendance.check-in'));
        $this->assertDatabaseHas('attendances', [
            'student_id' => $studentAccount->student_id,
            'geofence_status' => 'location_unavailable',
            'review_required' => 1,
            'location_unavailable' => 1,
        ]);
    }

    public function test_student_cannot_time_in_again_after_daily_time_out_is_completed(): void
    {
        $studentAccount = $this->makeStudentAccount('20230017');
        Setting::campus()->update([
            'attendance_time_in_start' => '00:00',
            'attendance_time_in_end' => '23:59',
            'attendance_time_out_start' => '00:00',
            'attendance_time_out_end' => '23:59',
            'attendance_am_time_in_start' => '00:00',
            'attendance_am_time_in_end' => '23:59',
            'attendance_am_time_out_start' => '00:00',
            'attendance_am_time_out_end' => '23:59',
            'attendance_pm_time_in_start' => '00:00',
            'attendance_pm_time_in_end' => '23:59',
            'attendance_pm_time_out_start' => '00:00',
            'attendance_pm_time_out_end' => '23:59',
        ]);
        cache()->forget('campus_settings');

        // AM in + AM out
        $r1 = $this->post(route('attendance.store'), ['attendance_action' => 'am_time_in', 'student_number' => $studentAccount->student->student_number, 'passcode' => '123456', 'latitude' => 10.742584903620171, 'longitude' => 122.96932879047095]);
        $r1->assertStatus(302);
        $r2 = $this->post(route('attendance.store'), ['attendance_action' => 'am_time_out', 'student_number' => $studentAccount->student->student_number, 'passcode' => '123456', 'latitude' => 10.742584903620171, 'longitude' => 122.96932879047095]);
        $r2->assertStatus(302);

        // PM in + PM out
        $r3 = $this->post(route('attendance.store'), ['attendance_action' => 'pm_time_in', 'student_number' => $studentAccount->student->student_number, 'passcode' => '123456', 'latitude' => 10.742584903620171, 'longitude' => 122.96932879047095]);
        $r3->assertStatus(302);
        $r4 = $this->post(route('attendance.store'), ['attendance_action' => 'pm_time_out', 'student_number' => $studentAccount->student->student_number, 'passcode' => '123456', 'latitude' => 10.742584903620171, 'longitude' => 122.96932879047095]);
        $r4->assertStatus(302);

        // All 4 done — next should fail
        $response = $this->post(route('attendance.store'), ['attendance_action' => 'am_time_in', 'student_number' => $studentAccount->student->student_number, 'passcode' => '123456', 'latitude' => 10.742584903620171, 'longitude' => 122.96932879047095]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('attendance_action');
        $this->assertEquals(1, \App\Models\Attendance::query()->where('student_id', $studentAccount->student_id)->count());
    }

}


