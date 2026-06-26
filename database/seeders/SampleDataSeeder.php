<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\Evaluation;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Login credentials (password for all: password)
     * - Instructor:  instructor@chmsu.edu.ph
     * - Chairperson: chairperson@chmsu.edu.ph
     * - Dean:        dean@chmsu.edu.ph
     */
    public function run(): void
    {
        $this->command->info('Seeding sample users and data...');
        // Keep seed data aligned with the reduced scope.
        User::query()->whereIn('role', ['employer', 'vpaa', 'cier', 'legal'])->delete();

        $existing = ['email_verified_at' => now(), 'first_login' => false];

        // --- Portal users (password: "password"; verified for dashboard access) ---
        $instructor = User::updateOrCreate(
            ['email' => 'instructor@chmsu.edu.ph'],
            array_merge(['name' => 'Instructor User', 'password' => 'password', 'role' => 'instructor'], $existing)
        );

        $chairperson = User::updateOrCreate(
            ['email' => 'chairperson@chmsu.edu.ph'],
            array_merge(['name' => 'Chairperson User', 'password' => 'password', 'role' => 'chairperson'], $existing)
        );

        $dean = User::updateOrCreate(
            ['email' => 'dean@chmsu.edu.ph'],
            array_merge(['name' => 'Santos, Maria', 'first_name' => 'Maria', 'last_name' => 'Santos', 'password' => 'password', 'role' => 'dean'], $existing)
        );

        // --- Students (structured names; display name is composed on save) ---
        $s1 = Student::updateOrCreate(
            ['student_number' => '20230001'],
            [
                'name' => 'Dela Cruz, Juan',
                'last_name' => 'Dela Cruz',
                'first_name' => 'Juan',
                'middle_name' => null,
                'name_extension' => null,
                'program' => 'BSIS',
                'year_level' => 4,
                'section' => 'A',
                'contact_number' => '09171234567',
                'assigned_instructor_id' => $instructor->id,
            ]
        );

        $s2 = Student::updateOrCreate(
            ['student_number' => '20230002'],
            [
                'name' => 'Santos, Maria',
                'last_name' => 'Santos',
                'first_name' => 'Maria',
                'middle_name' => null,
                'name_extension' => null,
                'program' => 'BSIS',
                'year_level' => 4,
                'section' => 'B',
                'contact_number' => '09187654321',
            ]
        );

        $s3 = Student::updateOrCreate(
            ['student_number' => '20230003'],
            [
                'name' => 'Reyes, Carlo',
                'last_name' => 'Reyes',
                'first_name' => 'Carlo',
                'middle_name' => null,
                'name_extension' => null,
                'program' => 'BSIS',
                'year_level' => 3,
                'section' => 'A',
                'contact_number' => '09201112233',
                'assigned_instructor_id' => $instructor->id,
            ]
        );

        $s4 = Student::updateOrCreate(
            ['student_number' => '20230004'],
            [
                'name' => 'Lim, Angela',
                'last_name' => 'Lim',
                'first_name' => 'Angela',
                'middle_name' => null,
                'name_extension' => null,
                'program' => 'BSIS',
                'year_level' => 3,
                'section' => 'C',
                'contact_number' => '09334455667',
                'assigned_instructor_id' => $instructor->id,
            ]
        );

        $s5 = Student::updateOrCreate(
            ['student_number' => '20230005'],
            [
                'name' => 'Villanueva, Mark',
                'last_name' => 'Villanueva',
                'first_name' => 'Mark',
                'middle_name' => null,
                'name_extension' => null,
                'program' => 'BSIS',
                'year_level' => 4,
                'section' => 'B',
                'contact_number' => '09556677889',
            ]
        );

        // --- Student portal accounts (default password = student_number) ---
        foreach ([$s1, $s2, $s3, $s4, $s5] as $student) {
            $needsPasscode = in_array($student->student_number, ['20230001', '20230003', '20230004'], true);
            $passcode = $needsPasscode ? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT) : null;
            StudentAccount::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'email' => 'student_'.(string) $student->student_number.'@example.com',
                    'password' => Hash::make((string) $student->student_number),
                    'is_active' => true,
                    'attendance_passcode' => $passcode,
                    'attendance_passcode_generated_at' => $passcode ? now() : null,
                ]
            );
        }

        // --- Companies ---
        $c1 = Company::updateOrCreate(
            ['name' => 'Tech Solutions Inc.'],
            [
                'address' => 'Bacolod City, Negros Occidental',
                'street_address' => 'Lacson Street',
                'barangay' => 'Mansilingan',
                'city_municipality' => 'Bacolod City',
                'contact_person' => 'Smith, Jane',
                'contact_last_name' => 'Smith',
                'contact_first_name' => 'Jane',
                'contact_email' => 'hr@techsolutions.ph',
                'contact_phone' => '0341234567',
                'is_active' => true,
            ]
        );

        $c2 = Company::updateOrCreate(
            ['name' => 'City Government IT Office'],
            [
                'address' => 'Talisay City Hall, Negros Occidental',
                'street_address' => 'City Hall Drive',
                'barangay' => 'Poblacion',
                'city_municipality' => 'Talisay City',
                'contact_person' => 'Reyes, Pedro',
                'contact_last_name' => 'Reyes',
                'contact_first_name' => 'Pedro',
                'contact_email' => 'it@talisay.gov.ph',
                'contact_phone' => '0349876543',
                'is_active' => true,
            ]
        );

        $c3 = Company::updateOrCreate(
            ['name' => 'DataCore Systems'],
            [
                'address' => 'Silay City, Negros Occidental',
                'street_address' => 'Mabini Avenue',
                'barangay' => 'Rizal',
                'city_municipality' => 'Silay City',
                'contact_person' => 'Cruz, Anna',
                'contact_last_name' => 'Cruz',
                'contact_first_name' => 'Anna',
                'contact_email' => 'anna@datacore.ph',
                'contact_phone' => '0341119999',
                'is_active' => true,
            ]
        );

        // --- Required documents ---
        // Matches your real "On-the-Job Training Checklist" (ignore any test documents).
        // Note: workflow queue steps only apply to documents that have a workflow_template_id mapping.
        // Remove the old OJT Supervisor Evaluation Form (replaced by automated HTE evaluation)
        RequiredDocument::where('name', 'OJT Supervisor Evaluation Form')->delete();

        // --- Pre-Requirements (8 documents) ---
        $docPledgeOfGoodConduct = RequiredDocument::updateOrCreate(
            ['name' => 'Pledge of Good Conduct'],
            ['description' => 'Signed pledge of good conduct (template)', 'is_mandatory' => true, 'order_index' => 1, 'phase' => 'pre']
        );
        $docMemorandumOfAgreement = RequiredDocument::updateOrCreate(
            ['name' => 'Memorandum of Agreement'],
            ['description' => 'Signed MOA between school and company', 'is_mandatory' => true, 'order_index' => 2, 'phase' => 'pre']
        );
        $docInternshipAgreement = RequiredDocument::updateOrCreate(
            ['name' => 'Internship Agreement'],
            ['description' => 'Internship agreement (notarized/routed for signatures)', 'is_mandatory' => true, 'order_index' => 3, 'phase' => 'pre']
        );
        $docParentConsentForm = RequiredDocument::updateOrCreate(
            ['name' => 'Parent Consent Form'],
            ['description' => 'Parent/guardian consent (notarized)', 'is_mandatory' => true, 'order_index' => 4, 'phase' => 'pre']
        );
        $docApplicationLetter = RequiredDocument::updateOrCreate(
            ['name' => 'Application Letter'],
            ['description' => 'Application letter addressed to host training establishment', 'is_mandatory' => true, 'order_index' => 5, 'phase' => 'pre']
        );
        $docEndorsementLetter = RequiredDocument::updateOrCreate(
            ['name' => 'Endorsement Letter'],
            ['description' => 'Official endorsement from the department', 'is_mandatory' => true, 'order_index' => 6, 'phase' => 'pre']
        );
        $docResume = RequiredDocument::updateOrCreate(
            ['name' => 'Resume'],
            ['description' => 'Student resume (personalized)', 'is_mandatory' => true, 'order_index' => 7, 'phase' => 'pre']
        );
        $docAcceptanceLetter = RequiredDocument::updateOrCreate(
            ['name' => 'Acceptance Letter'],
            ['description' => 'Acceptance letter from the host training establishment (with company logo)', 'is_mandatory' => true, 'order_index' => 8, 'phase' => 'pre']
        );

        // --- Monitoring Requirements (4 documents, visible after deployment) ---
        $docEnrollmentForm = RequiredDocument::updateOrCreate(
            ['name' => 'Enrolment Form'],
            ['description' => 'Student enrolment form for OJT', 'is_mandatory' => true, 'order_index' => 9, 'phase' => 'monitoring']
        );
        $docNbiClearance = RequiredDocument::updateOrCreate(
            ['name' => 'NBI Clearance'],
            ['description' => 'NBI clearance (if applicable to the student)', 'is_mandatory' => true, 'order_index' => 10, 'phase' => 'monitoring']
        );
        $docMedicalCertificate = RequiredDocument::updateOrCreate(
            ['name' => 'Medical Certificate'],
            ['description' => 'Medical certificate/clearance for internship', 'is_mandatory' => true, 'order_index' => 11, 'phase' => 'monitoring']
        );
        $docTrainingPlan = RequiredDocument::updateOrCreate(
            ['name' => 'Training Plan'],
            ['description' => 'OJT training/work plan (personalized)', 'is_mandatory' => true, 'order_index' => 12, 'phase' => 'monitoring']
        );

        // --- Post-Requirements (2 documents, visible after deployment completes) ---
        RequiredDocument::updateOrCreate(
            ['name' => 'Final DTR'],
            ['description' => 'Signed and accomplished Daily Time Record for the entire internship period', 'is_mandatory' => true, 'order_index' => 13, 'phase' => 'post']
        );
        RequiredDocument::updateOrCreate(
            ['name' => 'Final Weekly Journal'],
            ['description' => 'Compiled and signed weekly journals for the entire internship period', 'is_mandatory' => true, 'order_index' => 14, 'phase' => 'post']
        );

        // --- Deployments (unique: student_id + company_id + start_date) ---
        Deployment::updateOrCreate(
            [
                'student_id' => $s1->id,
                'company_id' => $c1->id,
                'start_date' => '2026-01-06',
            ],
            ['end_date' => '2026-03-28', 'status' => 'active', 'remarks' => 'On track, good attendance.']
        );

        Deployment::updateOrCreate(
            [
                'student_id' => $s3->id,
                'company_id' => $c2->id,
                'start_date' => '2026-01-13',
            ],
            ['end_date' => '2026-04-04', 'status' => 'active', 'remarks' => 'Assigned to IT support team.']
        );

        Deployment::updateOrCreate(
            [
                'student_id' => $s4->id,
                'company_id' => $c3->id,
                'start_date' => '2025-09-01',
            ],
            ['end_date' => '2025-11-30', 'status' => 'completed', 'remarks' => 'Completed with excellent evaluation.']
        );

        // --- Student documents (sample compliance data) ---
        // s1: mark the "original sample set" compliant, so existing UI demos still work.
        // (We intentionally limit sample compliance rows to a subset of the full checklist.)
        foreach ([$docMemorandumOfAgreement, $docParentConsentForm, $docEndorsementLetter] as $doc) {
            StudentDocument::firstOrCreate(
                ['student_id' => $s1->id, 'required_document_id' => $doc->id],
                ['status' => 'Submitted', 'submitted_at' => now()]
            );
        }

        // s3: partially compliant (2 of the original sample mandatory set)
        StudentDocument::firstOrCreate(
            ['student_id' => $s3->id, 'required_document_id' => $docMemorandumOfAgreement->id],
            ['status' => 'Submitted', 'submitted_at' => now()]
        );
        StudentDocument::firstOrCreate(
            ['student_id' => $s3->id, 'required_document_id' => $docParentConsentForm->id],
            ['status' => 'Submitted', 'submitted_at' => now()]
        );
        StudentDocument::firstOrCreate(
            ['student_id' => $s3->id, 'required_document_id' => $docEndorsementLetter->id],
            ['status' => 'Pending']
        );

        // s4: all mandatory submitted for the original sample set = compliant
        foreach ([$docMemorandumOfAgreement, $docParentConsentForm, $docEndorsementLetter] as $doc) {
            StudentDocument::firstOrCreate(
                ['student_id' => $s4->id, 'required_document_id' => $doc->id],
                ['status' => 'Submitted', 'submitted_at' => now()]
            );
        }

        // s2, s5: no documents = non-compliant

        // --- Evaluations ---
        Evaluation::firstOrCreate(
            ['student_id' => $s1->id, 'evaluation_type' => 'industry'],
            ['company_id' => $c1->id, 'evaluator_id' => $instructor->id, 'score' => 88, 'evaluated_at' => '2026-03-15', 'comments' => "Supervisor Name: Smith, Jane\nSupervisor Email: hr@techsolutions.ph\n\nJuan demonstrated excellent technical skills and was proactive in completing assigned tasks. He worked well with the team and showed great initiative."]
        );
        Evaluation::firstOrCreate(
            ['student_id' => $s1->id, 'evaluation_type' => 'school'],
            ['evaluator_id' => $instructor->id, 'score' => 92, 'evaluated_at' => '2026-03-20', 'comments' => 'Consistently submitted requirements on time. Active participation in class discussions.']
        );
        Evaluation::firstOrCreate(
            ['student_id' => $s4->id, 'evaluation_type' => 'industry'],
            ['company_id' => $c3->id, 'evaluator_id' => $instructor->id, 'score' => 95, 'evaluated_at' => '2025-11-28', 'comments' => "Supervisor Name: Cruz, Anna\nSupervisor Email: anna@datacore.ph\n\nAngela exceeded expectations. She delivered high-quality work and adapted quickly to the team's workflow."]
        );

        // --- Announcement ---
        Announcement::firstOrCreate(
            ['title' => 'Welcome to the Internship System', 'created_by' => $instructor->id],
            ['body' => 'This system manages BSIS student deployments, document compliance, and performance evaluations. Contact your instructor for any questions.', 'visible_to_role' => null]
        );

        Announcement::firstOrCreate(
            ['title' => 'Document Submission Deadline', 'created_by' => $chairperson->id],
            ['body' => 'All mandatory documents must be submitted by March 15, 2026. Please coordinate with your assigned company supervisor.', 'visible_to_role' => null]
        );

        $this->command->info('Sample data seeded successfully.');
        $this->command->newLine();
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Instructor', 'instructor@chmsu.edu.ph', 'password'],
                ['Chairperson', 'chairperson@chmsu.edu.ph', 'password'],
                ['Dean', 'dean@chmsu.edu.ph', 'password'],
            ]
        );
        $this->command->info('3 sample staff users, 5 students, 3 companies, 3 deployments, 12 required documents, 3 evaluations, and compliance data seeded.');
    }
}
