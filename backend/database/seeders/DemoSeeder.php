<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\DailyTimeRecord;
use App\Models\Deployment;
use App\Models\Evaluation;
use App\Models\EvaluationCriterion;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\StudentDocument;
use App\Models\StudentDocumentAction;
use App\Models\StudentRiskFlag;
use App\Models\User;
use App\Models\WeeklyJournal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private const STUDENT_PASSWORD = null; // null = defaults to student_number

    private array $studentDefs = [
        // Completed today
        [
            'sn' => '20230001', 'last' => 'Dela Cruz', 'first' => 'Juan', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'A',
            'phone' => '09171234567', 'instructor' => true, 'ojt' => 'external', 'company' => 0,
            'deploy_status' => 'completed', 'deploy_start' => '-120 days', 'deploy_end' => 'today',
            'pre_docs' => 'all', 'mon_docs' => 'all', 'post_docs' => 'all',
            'attendance_hours' => 600, 'cert' => true, 'eval' => 'both',
        ],
        // Pending — pre-docs uploaded, waiting approval
        [
            'sn' => '20230003', 'last' => 'Reyes', 'first' => 'Carlo', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'A',
            'phone' => '09201112233', 'instructor' => true, 'ojt' => 'external', 'company' => 0,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 5, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Pending — partial pre-docs
        [
            'sn' => '20230004', 'last' => 'Lim', 'first' => 'Angela', 'prog' => 'BSIS', 'yr' => 3, 'sec' => 'C',
            'phone' => '09334455667', 'instructor' => true, 'ojt' => 'external', 'company' => 2,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 3, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Pending — newly created, no docs
        [
            'sn' => '20230005', 'last' => 'Villanueva', 'first' => 'Mark', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'B',
            'phone' => '09556677889', 'instructor' => true, 'ojt' => 'external', 'company' => -1,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 0, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Pending — pre-docs all done, ready to auto-deploy (internal)
        [
            'sn' => '20230006', 'last' => 'Gomez', 'first' => 'Sarah', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'A',
            'phone' => '09778899001', 'instructor' => true, 'ojt' => 'internal', 'company' => -1,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 'all', 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Pending — just started, 2 pre-docs
        [
            'sn' => '20230007', 'last' => 'Tan', 'first' => 'James', 'prog' => 'BSIS', 'yr' => 3, 'sec' => 'D',
            'phone' => '09112233445', 'instructor' => true, 'ojt' => 'external', 'company' => -1,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 2, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Deployed — pre + 2 monitoring done
        [
            'sn' => '20230008', 'last' => 'Cruz', 'first' => 'Patricia', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'A',
            'phone' => '09335566778', 'instructor' => true, 'ojt' => 'external', 'company' => 0,
            'deploy_status' => 'active', 'deploy_start' => '-45 days', 'deploy_end' => '+60 days',
            'pre_docs' => 'all', 'mon_docs' => 2, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Deployed — pre + 1 monitoring done
        [
            'sn' => '20230009', 'last' => 'Lim', 'first' => 'Robert', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'B',
            'phone' => '09667788990', 'instructor' => true, 'ojt' => 'external', 'company' => 1,
            'deploy_status' => 'active', 'deploy_start' => '-30 days', 'deploy_end' => '+75 days',
            'pre_docs' => 'all', 'mon_docs' => 1, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Unassigned
        [
            'sn' => '20230011', 'last' => 'Flores', 'first' => 'Michael', 'prog' => 'BSIS', 'yr' => 4, 'sec' => 'D',
            'phone' => '09199887766', 'instructor' => true, 'ojt' => 'unplaced', 'company' => -1,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 0, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
        // Unassigned
        [
            'sn' => '20230012', 'last' => 'Torres', 'first' => 'Diane', 'prog' => 'BSIS', 'yr' => 3, 'sec' => 'B',
            'phone' => '09221234567', 'instructor' => true, 'ojt' => 'unplaced', 'company' => -1,
            'deploy_status' => 'pending', 'deploy_start' => null, 'deploy_end' => null,
            'pre_docs' => 0, 'mon_docs' => 0, 'post_docs' => 0,
            'attendance_hours' => 0, 'cert' => false, 'eval' => 'none',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        $instructor = User::where('email', 'instructor@chmsu.edu.ph')->first();
        if (! $instructor) {
            $this->command->error('Run SampleDataSeeder first to create staff users.');
            return;
        }

        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->command->error('Run SampleDataSeeder first to create companies.');
            return;
        }

        $this->cleanup();

        $preDocs = RequiredDocument::where('is_mandatory', true)->where('phase', 'pre')->orderBy('order_index')->get();
        $monitoringDocs = RequiredDocument::where('is_mandatory', true)->where('phase', 'monitoring')->orderBy('order_index')->get();
        $postDocs = RequiredDocument::where('is_mandatory', true)->where('phase', 'post')->orderBy('order_index')->get();

        foreach ($this->studentDefs as $i => $def) {
            $student = Student::create([
                'student_number' => $def['sn'],
                'name' => $def['last'].', '.$def['first'],
                'last_name' => $def['last'],
                'first_name' => $def['first'],
                'middle_name' => null,
                'name_extension' => null,
                'program' => $def['prog'],
                'year_level' => $def['yr'],
                'section' => $def['sec'],
                'contact_number' => $def['phone'],
                'assigned_instructor_id' => $def['instructor'] ? $instructor->id : null,
                'ojt_type' => $def['ojt'],
            ]);

            $password = self::STUDENT_PASSWORD ?? $def['sn'];
            StudentAccount::create([
                'student_id' => $student->id,
                'email' => 'student_'.$def['sn'].'@example.com',
                'password' => Hash::make($password),
                'is_active' => true,
            ]);

            $this->createStudentDocs($student, $def, $preDocs, $monitoringDocs, $postDocs);

            $this->createDeployment($student, $def, $companies, $instructor);

            if (($def['attendance_hours'] ?? 0) > 0) {
                $this->createAttendance($student, $def, $companies);
                $this->createWeeklyJournals($student, $def);
            }

            if (! empty($def['cert'])) {
                $this->createCertificate($student, $instructor);
            }

            if ($def['eval'] !== 'none') {
                $this->createEvaluation($student, $def, $companies, $instructor);
            }
        }

        $this->command->info('Demo data seeded: 12 students created.');
    }

    private function cleanup(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $studentIds = Student::query()->pluck('id');

        StudentRiskFlag::whereIn('student_id', $studentIds)->delete();
        Certificate::whereIn('student_id', $studentIds)->delete();
        Evaluation::whereIn('student_id', $studentIds)->delete();
        WeeklyJournal::whereIn('student_id', $studentIds)->delete();
        DailyTimeRecord::whereIn('student_id', $studentIds)->delete();
        Attendance::whereIn('student_id', $studentIds)->delete();

        $docIds = StudentDocument::whereIn('student_id', $studentIds)->pluck('id');
        StudentDocumentAction::whereIn('student_document_id', $docIds)->delete();
        StudentDocument::whereIn('student_id', $studentIds)->delete();

        Deployment::whereIn('student_id', $studentIds)->delete();
        StudentAccount::whereIn('student_id', $studentIds)->delete();
        Student::query()->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createStudentDocs(
        Student $student, array $def,
        $preDocs, $monitoringDocs, $postDocs
    ): void {
        $now = now();

        $phaseMap = ['pre' => ['docs' => $preDocs, 'count' => $def['pre_docs']]];
        if ($def['pre_docs'] === 'all' || (is_int($def['pre_docs']) && $def['pre_docs'] > 0)) {
            $phaseMap['monitoring'] = ['docs' => $monitoringDocs, 'count' => $def['mon_docs']];
        }
        if ($def['pre_docs'] === 'all' && $def['mon_docs'] === 'all') {
            $phaseMap['post'] = ['docs' => $postDocs, 'count' => $def['post_docs']];
        }

        foreach ($phaseMap as $phase => $cfg) {
            $take = $cfg['count'];
            if ($take === 'all') {
                $take = $cfg['docs']->count();
            }
            if ($take <= 0) {
                continue;
            }

            $docsToCreate = $cfg['docs']->take($take);
            $isCompleted = $def['deploy_status'] === 'completed';

            foreach ($docsToCreate as $rd) {
                StudentDocument::create([
                    'student_id' => $student->id,
                    'required_document_id' => $rd->id,
                    'status' => 'Submitted',
                    'workflow_status' => $isCompleted ? 'completed' : (in_array($def['deploy_status'], ['active', 'pending']) && $phase === 'pre' && $def['pre_docs'] === 'all' ? 'completed' : null),
                    'file_path' => 'demo/sample.pdf',
                    'submitted_at' => $now,
                    'last_action_at' => $now,
                ]);
            }
        }
    }

    private function createDeployment(Student $student, array $def, $companies, $instructor): void
    {
        $companyId = null;
        if ($def['company'] >= 0 && $def['company'] < $companies->count()) {
            $companyId = $companies[$def['company']]->id;
        }

        $startDate = $def['deploy_start'] ? Carbon::parse($def['deploy_start']) : Carbon::now()->addDays(60);
        $endDate = $def['deploy_end'] ? Carbon::parse($def['deploy_end']) : null;

        Deployment::withoutEvents(function () use ($student, $companyId, $startDate, $endDate, $def) {
            Deployment::create([
                'student_id' => $student->id,
                'company_id' => $companyId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $def['deploy_status'],
                'remarks' => match ($def['deploy_status']) {
                    'completed' => 'Successfully completed the internship program.',
                    'active' => 'Actively deployed — monitoring in progress.',
                    default => null,
                },
            ]);
        });

        // Fire saved event manually for active deployments to create journals + passcode
        if ($def['deploy_status'] === 'active' && $startDate) {
            $deployment = Deployment::where('student_id', $student->id)->first();
            if ($deployment) {
                $deployment->saveQuietly();
                $account = $student->account;
                if ($account && empty($account->attendance_passcode)) {
                    $account->ensureAttendancePasscode();
                }
            }
        }
    }

    private function createAttendance(Student $student, array $def, $companies): void
    {
        $totalMinutes = $def['attendance_hours'] * 60;
        $startDate = $def['deploy_start'] ? Carbon::parse($def['deploy_start']) : Carbon::now()->subDays(120);
        $daysNeeded = (int) ceil($totalMinutes / 480); // 8 hours per day
        $minutesPerDay = min(480, (int) ceil($totalMinutes / max(1, $daysNeeded)));

        $deployment = Deployment::where('student_id', $student->id)->first();

        for ($d = 0; $d < $daysNeeded && $d < 200; $d++) {
            $date = $startDate->copy()->addWeekdays($d);
            if ($date->isFuture()) {
                break;
            }

            $amIn = $date->copy()->setHour(8)->setMinute(0);
            $amOut = $date->copy()->setHour(12)->setMinute(0);
            $pmIn = $date->copy()->setHour(13)->setMinute(0);
            $pmOut = $date->copy()->setHour(17)->setMinute(0);

            Attendance::create([
                'student_id' => $student->id,
                'check_in_at' => $amIn,
                'time_out_at' => $pmOut,
                'am_check_in' => $amIn,
                'am_check_out' => $amOut,
                'pm_check_in' => $pmIn,
                'pm_check_out' => $pmOut,
                'total_minutes' => $minutesPerDay,
                'geofence_status' => 'inside_pass',
                'resolution_status' => 'resolved',
            ]);

            if ($deployment) {
                DailyTimeRecord::firstOrCreate(
                    ['student_id' => $student->id, 'date' => $date->format('Y-m-d')],
                    [
                        'deployment_id' => $deployment->id,
                        'source' => 'attendance',
                        'am_arrival' => $amIn->format('H:i:s'),
                        'am_departure' => $amOut->format('H:i:s'),
                        'pm_arrival' => $pmIn->format('H:i:s'),
                        'pm_departure' => $pmOut->format('H:i:s'),
                        'total_minutes' => $minutesPerDay,
                    ]
                );
            }
        }
    }

    private function createWeeklyJournals(Student $student, array $def): void
    {
        $deployment = Deployment::where('student_id', $student->id)->first();
        if (! $deployment || ! $deployment->start_date) {
            return;
        }

        $startDate = Carbon::parse($deployment->start_date);
        $endDate = $deployment->end_date ? Carbon::parse($deployment->end_date) : Carbon::now();
        $weekNumber = 1;

        $sampleActivities = [
            'Assisted in data encoding and file organization.',
            'Attended team meeting and discussed project milestones.',
            'Completed assigned tasks for database maintenance.',
            'Prepared reports and documentation for supervisor review.',
            'Participated in training session on system operations.',
            'Consolidated weekly deliverables and submitted progress report.',
        ];

        $cursor = $startDate->copy()->startOfWeek(Carbon::MONDAY);
        while ($cursor->lte($endDate)) {
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SATURDAY);
            $activities = [];
            $day = $cursor->copy();
            for ($i = 0; $i < 6 && $day->lte($endDate); $i++) {
                $activities[$day->format('Y-m-d')] = $sampleActivities[$i % count($sampleActivities)];
                $day->addDay();
            }

            $isComplete = $def['deploy_status'] === 'completed' || $def['attendance_hours'] > 0;

            $instructor = User::where('email', 'instructor@chmsu.edu.ph')->first();

            WeeklyJournal::firstOrCreate(
                ['student_id' => $student->id, 'week_start_date' => $cursor->format('Y-m-d')],
                [
                    'deployment_id' => $deployment->id,
                    'week_end_date' => $weekEnd->format('Y-m-d'),
                    'week_number' => $weekNumber,
                    'activities' => $activities,
                    'status' => $isComplete ? 'reviewed' : 'draft',
                    'submitted_at' => $isComplete ? $weekEnd->copy()->addDay() : null,
                    'reviewed_by' => $isComplete && $instructor ? $instructor->id : null,
                    'reviewed_at' => $isComplete ? $weekEnd->copy()->addDays(2) : null,
                ]
            );

            $weekNumber++;
            $cursor->addWeek();
        }
    }

    private function createCertificate(Student $student, $instructor): void
    {
        $deployment = Deployment::where('student_id', $student->id)->first();
        Certificate::create([
            'student_id' => $student->id,
            'deployment_id' => $deployment?->id,
            'title' => 'Certificate of Completion',
            'type' => 'completion',
            'file_path' => 'certificates/sample.pdf',
            'uploaded_by' => $instructor->id,
            'issued_at' => now(),
            'verified_at' => now(),
            'verified_by' => $instructor->id,
        ]);
    }

    private function createEvaluation(Student $student, array $def, $companies, $instructor): void
    {
        $companyId = null;
        if ($def['company'] >= 0 && $def['company'] < $companies->count()) {
            $companyId = $companies[$def['company']]->id;
        }

        if ($def['eval'] === 'both' || $def['eval'] === 'industry') {
            $criteria = EvaluationCriterion::where('is_active', true)->pluck('item_key', 'id')->toArray();
            $scores = [];
            foreach ($criteria as $id => $key) {
                $scores[$key] = rand(3, 5);
            }

            Evaluation::create([
                'student_id' => $student->id,
                'company_id' => $companyId,
                'evaluator_id' => $instructor->id,
                'evaluation_type' => 'industry',
                'score' => 90,
                'criteria_scores' => $scores,
                'evaluated_at' => now()->subDays(5),
                'comments' => 'Supervisor Name: Evaluator, Sample'.PHP_EOL.'Supervisor Email: evaluator@company.ph'.PHP_EOL.PHP_EOL.'The student demonstrated excellent performance throughout the internship period.',
            ]);
        }

        if ($def['eval'] === 'both' || $def['eval'] === 'school') {
            Evaluation::create([
                'student_id' => $student->id,
                'evaluator_id' => $instructor->id,
                'evaluation_type' => 'school',
                'score' => 92,
                'evaluated_at' => now()->subDays(3),
                'comments' => 'Consistently submitted requirements on time. Active participation in class.',
            ]);
        }
    }
}
