<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\RequiredDocument;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\StudentDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceGeofenceReviewTest extends TestCase
{
    use RefreshDatabase;

    private function deployedStudentWithPasscode(): StudentAccount
    {
        $student = Student::query()->create([
            'last_name' => 'Geo',
            'first_name' => 'Student',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20236601',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'contact_number' => '09123456789',
        ]);

        // Create mandatory Pre documents so Deployment saving hook allows active status
        $preDocNames = [
            'Pledge of Good Conduct', 'Memorandum of Agreement', 'Internship Agreement',
            'Parent Consent Form', 'Application Letter', 'Endorsement Letter',
            'Resume', 'Acceptance Letter',
        ];
        $preDocIds = [];
        foreach ($preDocNames as $i => $name) {
            $doc = RequiredDocument::create([
                'name' => $name,
                'description' => $name,
                'is_mandatory' => true,
                'phase' => 'pre',
                'order_index' => $i + 1,
            ]);
            $preDocIds[] = $doc->id;
        }
        // Mark all Pre docs as Submitted with completed workflow so areAllPreDocsApproved() returns true
        foreach ($preDocIds as $docId) {
            StudentDocument::create([
                'student_id' => $student->id,
                'required_document_id' => $docId,
                'file_path' => 'test/placeholder.pdf',
                'original_name' => 'placeholder.pdf',
                'status' => 'Submitted',
                'workflow_status' => 'completed',
            ]);
        }

        $company = \App\Models\Company::create([
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

        \App\Models\Deployment::create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'status' => 'active',
        ]);

        return StudentAccount::query()->create([
            'student_id' => $student->id,
            'email' => null,
            'password' => bcrypt('password'),
            'is_active' => true,
            'attendance_passcode' => '654321',
            'attendance_passcode_generated_at' => now(),
            'first_login' => false,
        ]);
    }

    public function test_instructor_can_resolve_pending_geofence_review(): void
    {
        Setting::campus()->update([
            'campus_lat' => 10.742584903620171,
            'campus_lng' => 122.96932879047095,
            'student_geofence_radius_meters' => 100,
            'geofence_review_buffer_meters' => 25,
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

        $account = $this->deployedStudentWithPasscode();
        $instructor = User::factory()->create(['role' => 'instructor']);

        $this->post(route('attendance.store'), [
            'attendance_action' => 'time_in',
            'student_number' => $account->student->student_number,
            'passcode' => '654321',
            'latitude' => null,
            'longitude' => null,
        ])->assertRedirect(route('attendance.check-in'));

        $attendance = Attendance::query()->where('student_id', $account->student_id)->firstOrFail();
        $this->assertTrue($attendance->review_required);
        $this->assertSame('pending', $attendance->resolution_status);

        $this->actingAs($instructor)->post(route('attendance.resolve', $attendance), [
            'resolution_note' => 'Verified with supervisor.',
        ])->assertRedirect();

        $attendance->refresh();
        $this->assertFalse($attendance->review_required);
        $this->assertSame('resolved', $attendance->resolution_status);
        $this->assertSame((int) $instructor->id, (int) $attendance->resolved_by);
        $this->assertSame('Verified with supervisor.', $attendance->resolution_note);
    }

    public function test_dean_cannot_post_resolve(): void
    {
        $account = $this->deployedStudentWithPasscode();
        $dean = User::factory()->create(['role' => 'dean']);

        $attendance = Attendance::query()->create([
            'student_id' => $account->student_id,
            'check_in_at' => Carbon::now(),
            'latitude' => null,
            'longitude' => null,
            'geofence_status' => 'location_unavailable',
            'review_required' => true,
            'resolution_status' => 'pending',
            'location_unavailable' => true,
        ]);

        $this->actingAs($dean)->post(route('attendance.resolve', $attendance), [
            'resolution_note' => 'Should fail',
        ])->assertForbidden();
    }

    public function test_geofence_near_boundary_uses_campus_buffer_meters_from_settings(): void
    {
        Setting::campus()->update([
            'campus_lat' => 10.742584903620171,
            'campus_lng' => 122.96932879047095,
            'student_geofence_radius_meters' => 100,
            'geofence_review_buffer_meters' => 50,
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

        $account = $this->deployedStudentWithPasscode();

        $lat = 10.742584903620171;
        $lng = 122.96932879047095;
        // ~124 m north â€” outside 100 m pass, inside 100+50 m review band (would be `outside_flagged` with only a 10 m buffer)
        $offsetLat = $lat + 0.00112;

        $this->post(route('attendance.store'), [
            'attendance_action' => 'time_in',
            'student_number' => $account->student->student_number,
            'passcode' => '654321',
            'latitude' => $offsetLat,
            'longitude' => $lng,
            'accuracy_meters' => 5,
        ])->assertRedirect(route('attendance.check-in'));

        $row = Attendance::query()->where('student_id', $account->student_id)->firstOrFail();
        $this->assertSame('near_boundary_review', $row->geofence_status);
    }

    public function test_attendance_index_supports_page_two_for_normal_and_htmx_requests(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);

        for ($i = 1; $i <= 12; $i++) {
            $student = Student::query()->create([
                'last_name' => 'Student',
                'first_name' => 'Test '.$i,
                'middle_name' => null,
                'name_extension' => null,
                'student_number' => str_pad((string) (20230000 + $i), 8, '0', STR_PAD_LEFT),
                'program' => 'BSIS',
                'year_level' => 4,
                'section' => 'A',
                'contact_number' => '09123456789',
            ]);

            Attendance::query()->create([
                'student_id' => $student->id,
                'check_in_at' => Carbon::now()->subMinutes($i),
                'time_out_at' => Carbon::now()->subMinutes(max(0, $i - 1)),
                'geofence_status' => 'inside_pass',
                'review_required' => false,
                'resolution_status' => 'not_needed',
                'location_unavailable' => false,
                'total_minutes' => 30,
            ]);
        }

        $this->actingAs($instructor)
            ->get(route('attendance.index', ['review_scope' => 'all', 'page' => 3]))
            ->assertOk()
            ->assertSee('20230011');

        $this->actingAs($instructor)
            ->withHeader('HX-Request', 'true')
            ->get(route('attendance.index', ['review_scope' => 'all', 'page' => 3]))
            ->assertOk()
            ->assertSee('20230011')
            ->assertDontSee('Campus Attendance Log');
    }
}
