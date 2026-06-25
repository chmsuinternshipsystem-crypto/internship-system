<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Deployment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Services\GeofencingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    // Public/lightweight clock-in form (separate from full student portal)
    public function create()
    {
        $campus = Setting::campus();

        $student = null;
        $studentAccountId = (int) session('student_account_id', 0);
        if ($studentAccountId > 0) {
            $studentAccount = StudentAccount::with('student')->find($studentAccountId);
            $student = $studentAccount?->student;
        }

        $todayStatus = null;
        $todayAttendance = null;
        if ($student) {
            $todayAttendance = Attendance::query()
                ->where('student_id', $student->id)
                ->whereDate('check_in_at', today())
                ->latest('check_in_at')
                ->first();

            $todayStatus = match (true) {
                !$todayAttendance => 'not_checked_in',
                $todayAttendance->time_out_at !== null => 'completed',
                default => 'checked_in',
            };
        }

        $result = session('attendance_result');

        $sessionLabel = null;
        $sessionType = null;
        if ($student && $student->id) {
            $session = $this->resolveSessionState($student->id);
            if ($session) {
                $sessionLabel = $session['label'];
                $sessionType = str_starts_with((string) $session['type'], 'am') ? 'am' : 'pm';
            }
        }

        return view('attendance.check-in', compact(
            'campus', 'student', 'todayStatus', 'todayAttendance', 'result', 'sessionLabel', 'sessionType'
        ));
    }

    // Handle clock in / clock out submission
    public function store(Request $request)
    {
        $studentAccountId = (int) session('student_account_id', 0);
        $isLoggedIn = $studentAccountId > 0;

        $allowedActions = 'am_time_in,am_time_out,pm_time_in,pm_time_out,time_in,time_out';

        if ($isLoggedIn) {
            $data = $request->validate([
                'attendance_action' => ['required', 'in:'.$allowedActions],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            ]);

            $studentAccount = StudentAccount::with('student')->find($studentAccountId);
            if (! $studentAccount || ! $studentAccount->is_active || ! $studentAccount->student) {
                return back()->withErrors(['student_number' => __('Your session is invalid. Please sign in again.')]);
            }
            $student = $studentAccount->student;
        } else {
            $data = $request->validate([
                'attendance_action' => ['required', 'in:'.$allowedActions],
                'student_number' => ['required', 'digits:8'],
                'passcode' => ['required', 'digits:6'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'accuracy_meters' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            ]);

            $student = Student::where('student_number', $data['student_number'])->first();
            if (! $student) {
                return back()->withInput()->withErrors(['student_number' => __('Student number not found.')]);
            }

            $studentAccount = StudentAccount::query()->where('student_id', $student->id)->first();
            if (! $studentAccount || ! $studentAccount->is_active) {
                return back()->withInput()->withErrors(['student_number' => __('Student account is not active.')]);
            }

            $expectedPasscode = $studentAccount->ensureAttendancePasscode();
            if (! hash_equals($expectedPasscode, (string) $data['passcode'])) {
                return back()->withInput()->withErrors(['passcode' => __('Invalid attendance passcode.')]);
            }
        }

        if (! $student->isDeploymentEligibleForPortal()) {
            return back()->withInput()->withErrors(['student_number' => __('Clock in/out is available only for deployed students.')]);
        }

        $campus = \App\Models\Setting::campusCached();

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $accuracy = isset($data['accuracy_meters']) ? (int) round((float) $data['accuracy_meters']) : null;

        $rawAction = (string) $data['attendance_action'];
        $session = $this->resolveSessionFromAction($rawAction, $student->id);

        if (! $session) {
            return back()
                ->withInput()
                ->withErrors(['attendance_action' => __('Attendance already completed for today.')]);
        }

        // Legacy action validation
        $todayAtt = $session['attendance'];
        if ($rawAction === 'time_in' && $todayAtt && $todayAtt->am_check_in && !$todayAtt->am_check_out) {
            return back()
                ->withInput()
                ->withErrors(['attendance_action' => __('You already started a session. Please submit a Time Out entry.')]);
        }

        if ($rawAction === 'time_out' && ! $todayAtt) {
            return back()
                ->withInput()
                ->withErrors(['attendance_action' => __('No active Time In found for today. Please submit Time In first.')]);
        }

        // Time window check
        $now = Carbon::now();
        $windowField = match ($session['type']) {
            'am_time_in' => 'attendance_am_time_in',
            'am_time_out' => 'attendance_am_time_out',
            'pm_time_in' => 'attendance_pm_time_in',
            'pm_time_out' => 'attendance_pm_time_out',
        };
        $windowStart = $campus->{$windowField . '_start'};
        $windowEnd = $campus->{$windowField . '_end'};
        $graceMinutes = (int) ($campus->attendance_grace_minutes ?? 60);

        $nowTime = $now->format('H:i');
        $graceEnd = Carbon::parse($windowEnd)->addMinutes($graceMinutes);

        if ($nowTime < $windowStart || $now->gt($graceEnd)) {
            return back()->withInput()->withErrors([
                'attendance_action' => __('Clock-:session is closed. Expected window is :start – :end with a :grace-min grace period.', [
                    'session' => $session['label'],
                    'start' => $windowStart,
                    'end' => $windowEnd,
                    'grace' => $graceMinutes,
                ]),
            ]);
        }

        $timeOutsideWindow = $now->gt(Carbon::parse($windowEnd));

        $activeDeployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->with('company')
            ->first();

        $company = $activeDeployment?->company;
        if ($company && !$company->geofencing_enabled) {
            $decision = [
                'geofence_status' => 'location_unavailable',
                'review_required' => false,
                'resolution_status' => 'not_needed',
                'is_within_campus' => false,
                'location_unavailable' => true,
                'distance_meters' => null,
            ];
        } else {
            $decision = $this->resolveGeofence($latitude, $longitude, $campus, $activeDeployment);
        }

        $accuracySuspicious = $accuracy !== null && $accuracy < 1;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        if (in_array($session['type'], ['am_time_in', 'pm_time_in'], true)) {
            $existingAtt = $session['attendance'];
            $isNew = ! $existingAtt;
            $attData = [
                'student_id' => $student->id,
                $session['type'] === 'am_time_in' ? 'am_check_in' : 'pm_check_in' => Carbon::now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $decision['distance_meters'] !== null ? (int) round((float) $decision['distance_meters']) : null,
                'is_within_campus' => $decision['is_within_campus'],
                'location_unavailable' => $decision['location_unavailable'],
                'geofence_status' => $decision['geofence_status'],
                'review_required' => $decision['review_required'],
                'resolution_status' => $decision['resolution_status'],
                'time_outside_window' => $timeOutsideWindow,
                'accuracy_suspicious' => $accuracySuspicious,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ];

            if ($isNew) {
                $attData['check_in_at'] = Carbon::now();
                $attendance = Attendance::create($attData);
            } else {
                $existingAtt->update($attData);
                $attendance = $existingAtt;
            }

            $result = [
                'action' => $session['type'],
                'action_label' => $session['label'],
                'time' => $now->format('h:i A'),
                'student_name' => $student->name,
                'student_number' => $student->student_number,
                'date' => today()->format('F d, Y'),
                'geofence_status' => $decision['geofence_status'],
                'is_within_campus' => $decision['is_within_campus'],
                'location_unavailable' => $decision['location_unavailable'],
                'total_minutes' => null,
            ];

            return redirect()
                ->route('attendance.check-in')
                ->with('attendance_result', $result);
        }

        // Time-out (am_out or pm_out)
        $todayAttendance = $session['attendance'];
        $checkInField = $session['type'] === 'am_time_out' ? 'am_check_in' : 'pm_check_in';
        $sessionStart = $todayAttendance->{$checkInField};

        $minutesRendered = $sessionStart
            ? (int) max(0, Carbon::parse($sessionStart)->diffInMinutes($now))
            : 0;

        $timeOutField = $session['type'] === 'am_time_out' ? 'am_check_out' : 'pm_check_out';
        $timeOutGeofenceStatus = $decision['geofence_status'];
        $reviewRequired = $todayAttendance->review_required || $decision['review_required'];
        $resolutionStatus = $reviewRequired ? 'pending' : 'not_needed';

        $updateData = [
            $timeOutField => $now,
            'time_out_at' => $session['type'] === 'pm_time_out' ? $now : $todayAttendance->time_out_at,
            'time_out_latitude' => $latitude,
            'time_out_longitude' => $longitude,
            'time_out_accuracy_meters' => $accuracy,
            'time_out_distance_meters' => $decision['distance_meters'] !== null ? (int) round((float) $decision['distance_meters']) : null,
            'time_out_geofence_status' => $timeOutGeofenceStatus,
            'review_required' => $reviewRequired,
            'resolution_status' => $resolutionStatus,
            'total_minutes' => ($todayAttendance->total_minutes ?? 0) + $minutesRendered,
            'time_outside_window' => $timeOutsideWindow,
            'accuracy_suspicious' => $accuracySuspicious,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ];

        $todayAttendance->update($updateData);

        // Auto-complete deployment if 600 hours reached
        $totalMinutes = $student->getTotalAttendanceMinutes();
        if ($totalMinutes >= 36000) {
            $activeDeployment = $student->deployments()
                ->where('status', 'active')
                ->first();

            if ($activeDeployment) {
                $activeDeployment->forceFill([
                    'status' => 'completed',
                    'end_date' => now()->toDateString(),
                ])->save();
            }
        }

        $result = [
            'action' => $session['type'],
            'action_label' => $session['label'],
            'time' => $now->format('h:i A'),
            'student_name' => $student->name,
            'student_number' => $student->student_number,
            'date' => today()->format('F d, Y'),
            'geofence_status' => $timeOutGeofenceStatus,
            'is_within_campus' => $decision['is_within_campus'],
            'location_unavailable' => $decision['location_unavailable'],
            'total_minutes' => $minutesRendered,
        ];

        return redirect()
            ->route('attendance.check-in')
            ->with('attendance_result', $result);
    }

    // Quick clock in/out from student dashboard (session-authenticated, no passcode)
    public function quickClock(Request $request)
    {
        $student = $request->attributes->get('student');
        if (! $student) {
            return response()->json(['error' => 'Student not authenticated.'], 403);
        }

        if (! $student->isDeploymentEligibleForPortal()) {
            return response()->json(['error' => 'Clock in/out is available only for deployed students.'], 403);
        }

        $latitude = $request->float('latitude');
        $longitude = $request->float('longitude');
        if ($latitude !== 0.0 && ($latitude < -90 || $latitude > 90)) {
            return response()->json(['error' => 'Invalid latitude value.'], 422);
        }
        if ($longitude !== 0.0 && ($longitude < -180 || $longitude > 180)) {
            return response()->json(['error' => 'Invalid longitude value.'], 422);
        }
        $accuracy = $request->integer('accuracy_meters', 0) ?: null;

        $session = $this->resolveSessionState($student->id);

        if (! $session) {
            return response()->json(['error' => __('Attendance already completed for today.')], 422);
        }

        $campus = \App\Models\Setting::campusCached();
        $activeDeployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->with('company')
            ->first();

        // Time window check
        $now = Carbon::now();
        $windowField = match ($session['type']) {
            'am_time_in' => 'attendance_am_time_in',
            'am_time_out' => 'attendance_am_time_out',
            'pm_time_in' => 'attendance_pm_time_in',
            'pm_time_out' => 'attendance_pm_time_out',
        };
        $windowStart = $campus->{$windowField . '_start'};
        $windowEnd = $campus->{$windowField . '_end'};
        $graceMinutes = (int) ($campus->attendance_grace_minutes ?? 60);

        $nowTime = $now->format('H:i');
        $graceEnd = Carbon::parse($windowEnd)->addMinutes($graceMinutes);

        if ($nowTime < $windowStart || $now->gt($graceEnd)) {
            return response()->json(['error' => __(':session is closed right now.', ['session' => $session['label']])], 422);
        }

        $timeOutsideWindow = $now->gt(Carbon::parse($windowEnd));

        // Geofence with toggle support
        $company = $activeDeployment?->company;
        if ($company && !$company->geofencing_enabled) {
            $decision = [
                'geofence_status' => 'location_unavailable',
                'review_required' => false,
                'resolution_status' => 'not_needed',
                'is_within_campus' => false,
                'location_unavailable' => true,
                'distance_meters' => null,
            ];
        } else {
            $decision = $this->resolveGeofence($latitude, $longitude, $campus, $activeDeployment);
        }

        $accuracySuspicious = $accuracy !== null && $accuracy < 1;
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        if (in_array($session['type'], ['am_time_in', 'pm_time_in'], true)) {
            $existingAtt = $session['attendance'];
            $attData = [
                'student_id' => $student->id,
                $session['type'] === 'am_time_in' ? 'am_check_in' : 'pm_check_in' => Carbon::now(),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => $accuracy,
                'distance_meters' => $decision['distance_meters'] !== null ? (int) round((float) $decision['distance_meters']) : null,
                'is_within_campus' => $decision['is_within_campus'],
                'location_unavailable' => $decision['location_unavailable'],
                'geofence_status' => $decision['geofence_status'],
                'review_required' => $decision['review_required'],
                'resolution_status' => $decision['resolution_status'],
                'time_outside_window' => $timeOutsideWindow,
                'accuracy_suspicious' => $accuracySuspicious,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ];

            if ($existingAtt) {
                $existingAtt->update($attData);
                $attendance = $existingAtt;
            } else {
                $attData['check_in_at'] = Carbon::now();
                $attendance = Attendance::create($attData);
            }
            $totalMinutes = null;
        } else {
            $todayAttendance = $session['attendance'];
        $checkInField = $session['type'] === 'am_time_out' ? 'am_check_in' : 'pm_check_in';
        $sessionStart = $todayAttendance->{$checkInField};
        $minutesRendered = $sessionStart
            ? (int) max(0, Carbon::parse($sessionStart)->diffInMinutes($now))
            : 0;
        $timeOutField = $session['type'] === 'am_time_out' ? 'am_check_out' : 'pm_check_out';
        $timeOutGeofenceStatus = $decision['geofence_status'];
        $reviewRequired = $todayAttendance->review_required || $decision['review_required'];
        $resolutionStatus = $reviewRequired ? 'pending' : 'not_needed';

            $updateData = [
                $timeOutField => $now,
                'time_out_at' => $session['type'] === 'pm_time_out' ? $now : $todayAttendance->time_out_at,
                'time_out_latitude' => $latitude,
                'time_out_longitude' => $longitude,
                'time_out_accuracy_meters' => $accuracy,
                'time_out_distance_meters' => $decision['distance_meters'] !== null ? (int) round((float) $decision['distance_meters']) : null,
                'time_out_geofence_status' => $timeOutGeofenceStatus,
                'review_required' => $reviewRequired,
                'resolution_status' => $resolutionStatus,
                'total_minutes' => ($todayAttendance->total_minutes ?? 0) + $minutesRendered,
                'time_outside_window' => $timeOutsideWindow,
                'accuracy_suspicious' => $accuracySuspicious,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ];
            $todayAttendance->update($updateData);
            $totalMinutes = $minutesRendered;
            $attendance = $todayAttendance;
        }

        // Auto-complete deployment if 600 hours reached (only on PM time-out)
        if ($session['type'] === 'pm_time_out' && $student->getTotalAttendanceMinutes() >= 36000) {
            $activeDeployment = $student->deployments()
                ->where('status', 'active')
                ->first();
            if ($activeDeployment) {
                $activeDeployment->forceFill([
                    'status' => 'completed',
                    'end_date' => now()->toDateString(),
                ])->save();
            }
        }

        $hours = $totalMinutes !== null ? intdiv($totalMinutes, 60) : null;
        $mins = $totalMinutes !== null ? $totalMinutes % 60 : null;

        return response()->json([
            'success' => true,
            'action' => $session['type'],
            'action_label' => $session['label'],
            'time' => Carbon::now()->format('h:i A'),
            'date' => today()->format('F d, Y'),
            'geofence_status' => $decision['geofence_status'],
            'total_minutes' => $totalMinutes,
            'hours' => $hours,
            'mins' => $mins,
            'checked_in' => in_array($session['type'], ['am_time_in', 'pm_time_in'], true),
            'completed' => $session['type'] === 'pm_time_out',
            'passcode' => null,
        ]);
    }

    // Coordinator/staff attendance log
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        if (! in_array($status, ['inside_pass', 'near_boundary_review', 'outside_flagged', 'location_unavailable'], true)) {
            $status = '';
        }
        $studentQ = trim((string) $request->query('search', $request->query('q', '')));
        $attendanceDate = trim((string) $request->query('date', ''));
        $reviewScope = trim((string) $request->query('review_scope', ''));
        if (! in_array($reviewScope, ['needs_attention', 'open_time_ins', 'history', 'all'], true)) {
            $reviewScope = 'all';
        }

        $applyFilters = function ($query) use ($status, $studentQ, $attendanceDate): void {
            if ($status !== '') {
                $query->where('geofence_status', $status);
            }
            if ($studentQ !== '') {
                $query->whereHas('student', function ($studentQuery) use ($studentQ): void {
                    $studentQuery
                        ->where('student_number', 'like', '%'.$studentQ.'%')
                        ->orWhere('name', 'like', '%'.$studentQ.'%');
                });
            }
            if ($attendanceDate !== '') {
                $query->whereDate('check_in_at', $attendanceDate);
            }
        };

        $query = Attendance::query()->with(['student', 'resolver']);
        $applyFilters($query);

        if ($reviewScope === 'needs_attention') {
            $query->where(function ($scope): void {
                $scope->where(function ($pending): void {
                    $pending->where('review_required', true)
                        ->where('resolution_status', 'pending');
                })->orWhereNull('time_out_at');
            });
            $query->orderByRaw(
                "CASE
                    WHEN time_out_at IS NULL THEN 0
                    WHEN geofence_status = 'outside_flagged' THEN 1
                    WHEN geofence_status = 'location_unavailable' THEN 2
                    WHEN geofence_status = 'near_boundary_review' THEN 3
                    ELSE 4
                END"
            )->orderByDesc('check_in_at');
        } elseif ($reviewScope === 'open_time_ins') {
            $query->whereNull('time_out_at')->orderByDesc('check_in_at');
        } elseif ($reviewScope === 'history') {
            $query->whereNotNull('time_out_at')->orderByDesc('check_in_at');
        } else {
            $query->orderByDesc('check_in_at');
        }

        if ($request->boolean('my_students')) {
            $query->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }

        if ($request->boolean('poll')) {
            $latestUpdatedAt = (clone $query)->max('updated_at');

            return response()->json([
                'latest_updated_at' => $latestUpdatedAt ? \Carbon\Carbon::parse($latestUpdatedAt)->toIso8601String() : null,
                'review_scope' => $reviewScope,
            ]);
        }

        $summaryBase = Attendance::query();
        $applyFilters($summaryBase);
        if ($request->boolean('my_students')) {
            $summaryBase->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }
        $summary = [
            'needs_attention' => (clone $summaryBase)
                ->where(function ($scope): void {
                    $scope->where(function ($pending): void {
                        $pending->where('review_required', true)
                            ->where('resolution_status', 'pending');
                    })->orWhereNull('time_out_at');
                })
                ->count(),
            'open_time_ins' => (clone $summaryBase)
                ->whereNull('time_out_at')
                ->count(),
            'off_campus' => (clone $summaryBase)
                ->where('geofence_status', 'outside_flagged')
                ->count(),
            'resolved' => (clone $summaryBase)
                ->where('resolution_status', 'resolved')
                ->count(),
        ];

        $latestUpdatedAt = (clone $query)->max('updated_at');
        $attendances = $query->paginate(5)->withQueryString();
        $campus = Setting::campus();

        $myStudents = $request->boolean('my_students');

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('attendance.partials.ajax-list', compact(
                'attendances',
                'campus',
                'summary',
                'latestUpdatedAt',
                'myStudents',
            ));
        }

        return view('attendance.index', compact(
            'attendances',
            'status',
            'campus',
            'studentQ',
            'attendanceDate',
            'reviewScope',
            'summary',
            'latestUpdatedAt',
            'myStudents',
        ));
    }

    /**
     * @return array{geofence_status:string,review_required:bool,resolution_status:string,is_within_campus:?bool,location_unavailable:bool,distance_meters:?float}
     */
    private function resolveGeofence(?float $latitude, ?float $longitude, Setting $campus, ?Deployment $deployment): array
    {
        if ($latitude === null || $longitude === null) {
            return [
                'geofence_status' => 'location_unavailable',
                'review_required' => true,
                'resolution_status' => 'pending',
                'is_within_campus' => null,
                'location_unavailable' => true,
                'distance_meters' => null,
            ];
        }

        // School-based: use campus geofence
        if ($deployment && is_null($deployment->company_id)) {
            return (new GeofencingService())->checkPoint($latitude, $longitude, $campus);
        }

        // External company: use company GPS coordinates
        $company = $deployment?->company;
        if ($company && $company->latitude && $company->longitude) {
            $distanceMeters = GeofencingService::haversineDistanceMeters(
                (float) $company->latitude,
                (float) $company->longitude,
                $latitude,
                $longitude
            );
            $radius = $company->geofence_radius_meters ?? 100;
            $buffer = 50;

            if ($distanceMeters <= $radius) {
                return [
                    'geofence_status' => 'inside_pass',
                    'review_required' => false,
                    'resolution_status' => 'not_needed',
                    'is_within_campus' => false,
                    'location_unavailable' => false,
                    'distance_meters' => $distanceMeters,
                ];
            }

            if ($distanceMeters <= ($radius + $buffer)) {
                return [
                    'geofence_status' => 'near_boundary_review',
                    'review_required' => true,
                    'resolution_status' => 'pending',
                    'is_within_campus' => false,
                    'location_unavailable' => false,
                    'distance_meters' => $distanceMeters,
                ];
            }

            return [
                'geofence_status' => 'outside_flagged',
                'review_required' => true,
                'resolution_status' => 'pending',
                'is_within_campus' => false,
                'location_unavailable' => false,
                'distance_meters' => $distanceMeters,
            ];
        }

        // No geofence configuration available
        return [
            'geofence_status' => 'location_unavailable',
            'review_required' => true,
            'resolution_status' => 'pending',
            'is_within_campus' => null,
            'location_unavailable' => true,
            'distance_meters' => null,
        ];
    }

    /**
     * Instructor / Chairperson: clear a pending geofence or location review after manual verification.
     */
    public function export(Request $request)
    {
        $status = (string) $request->query('status', '');
        if (! in_array($status, ['inside_pass', 'near_boundary_review', 'outside_flagged', 'location_unavailable'], true)) {
            $status = '';
        }
        $studentQ = trim((string) $request->query('search', ''));
        $attendanceDate = trim((string) $request->query('date', ''));

        $query = Attendance::query()->with('student');

        if ($status !== '') {
            $query->where('geofence_status', $status);
        }
        if ($studentQ !== '') {
            $query->whereHas('student', function ($q) use ($studentQ): void {
                $q->where('student_number', 'like', '%'.$studentQ.'%')
                  ->orWhere('name', 'like', '%'.$studentQ.'%');
            });
        }
        if ($attendanceDate !== '') {
            $query->whereDate('check_in_at', $attendanceDate);
        }

        $query->orderByDesc('check_in_at');

        $response = new StreamedResponse(function () use ($query): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, [
                'Student Number', 'Student Name', 'Date', 'Time In', 'Time Out',
                'Total Minutes', 'Geofence Status', 'Review Required', 'Resolution Status',
            ]);

            $query->chunk(100, function ($records) use ($handle): void {
                foreach ($records as $a) {
                    fputcsv($handle, [
                        $a->student?->student_number,
                        $a->student?->name,
                        $a->check_in_at?->format('Y-m-d'),
                        $a->check_in_at?->format('h:i A'),
                        $a->time_out_at?->format('h:i A'),
                        $a->total_minutes,
                        $a->geofence_status,
                        $a->review_required ? __('Yes') : __('No'),
                        $a->resolution_status,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="attendance-export-'.now()->format('Y-m-d-His').'.csv"');

        return $response;
    }

    public function resolve(Request $request, Attendance $attendance)
    {
        $role = (string) (auth()->user()?->role ?? '');
        if (! in_array($role, ['instructor', 'chairperson'], true)) {
            abort(403);
        }

        if ($attendance->resolution_status !== 'pending' || ! $attendance->review_required) {
            return redirect()
                ->route('attendance.index')
                ->with('status', __('This record is not pending review.'));
        }

        $data = $request->validate([
            'resolution_note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance->update([
            'review_required' => false,
            'resolution_status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => Carbon::now(),
            'resolution_note' => isset($data['resolution_note']) ? trim((string) $data['resolution_note']) : null,
        ]);

        return redirect()
            ->back()
            ->with('status', __('Attendance review marked as resolved.'));
    }

    /**
     * Resolve the expected session action from the user-submitted action + current state.
     *
     * @return array{type:string,label:string,attendance:\App\Models\Attendance|null}|null
     */
    private function resolveSessionFromAction(string $rawAction, int $studentId): ?array
    {
        $today = Attendance::query()
            ->where('student_id', $studentId)
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->first();

        if (in_array($rawAction, ['am_time_in', 'am_time_out', 'pm_time_in', 'pm_time_out'], true)) {
            // Check if the day is already fully completed
            if ($today && $today->pm_check_in && $today->pm_check_out) {
                return null;
            }

            $labels = [
                'am_time_in' => __('AM Time In'),
                'am_time_out' => __('AM Time Out'),
                'pm_time_in' => __('PM Time In'),
                'pm_time_out' => __('PM Time Out'),
            ];

            return [
                'type' => $rawAction,
                'label' => $labels[$rawAction],
                'attendance' => $today,
            ];
        }

        // Legacy time_in/time_out → auto-detect session
        return $this->resolveSessionState($studentId);
    }

    /**
     * Auto-detect the next expected session based on the current attendance state.
     *
     * @return array{type:string,label:string,attendance:\App\Models\Attendance|null}|null
     */
    private function resolveSessionState(int $studentId): ?array
    {
        $today = Attendance::query()
            ->where('student_id', $studentId)
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->first();

        if (! $today) {
            return ['type' => 'am_time_in', 'label' => __('AM Time In'), 'attendance' => null];
        }

        if ($today->am_check_in && ! $today->am_check_out) {
            return ['type' => 'am_time_out', 'label' => __('AM Time Out'), 'attendance' => $today];
        }

        if ($today->am_check_out && $today->pm_check_in === null) {
            return ['type' => 'pm_time_in', 'label' => __('PM Time In'), 'attendance' => $today];
        }

        if ($today->pm_check_in && ! $today->pm_check_out) {
            return ['type' => 'pm_time_out', 'label' => __('PM Time Out'), 'attendance' => $today];
        }

        return null;
    }
}
