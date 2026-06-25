<?php

namespace App\Http\Middleware;

use App\Models\StudentAccount;
use App\Notifications\InAppNotification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check()) {
            $request->session()->forget('student_account_id');

            return redirect()
                ->route('dashboard')
                ->with('status', __('The student portal is only available when you sign in with a student number. Close this tab or use Sign out, then sign in as a student.'))
                ->with('status_type', 'error');
        }

        $studentAccountId = (int) $request->session()->get('student_account_id', 0);

        // Idle timeout — 30 min inactivity forces re-login
        if ($studentAccountId > 0) {
            $lastActivity = (int) $request->session()->get('student_last_activity', 0);
            if ($lastActivity > 0 && now()->diffInMinutes(now()->setTimestamp($lastActivity)) > 120) {
                $request->session()->forget(['student_account_id', 'student_last_activity']);
                return redirect()
                    ->route('login')
                    ->with('status', __('Your session expired due to inactivity. Please sign in again.'))
                    ->with('status_type', 'error');
            }
        }

        if ($studentAccountId <= 0) {
            return redirect()
                ->route('login')
                ->with('status', __('Your student session expired. Please sign in again.'))
                ->with('status_type', 'error');
        }

        $studentAccount = StudentAccount::query()
            ->with('student')
            ->find($studentAccountId);

        if (! $studentAccount || ! $studentAccount->is_active || ! $studentAccount->student) {
            $request->session()->forget('student_account_id');

            return redirect()
                ->route('login')
                ->with('status', __('Your student account is not available. Please contact your coordinator.'))
                ->with('status_type', 'error');
        }

        if ($studentAccount->isFirstLogin()) {
            $name = $request->route()?->getName();
            if (! in_array($name, ['student.password.change', 'student.password.update'], true)) {
                return redirect()->route('student.password.change');
            }
        }

        $student = $studentAccount->student;

        // Retroactive auto-deploy: if Pre docs are all approved and eligible, activate now
        $wasActivated = $student->autoActivateDeployment();

        // If pre-docs are done but student is unplaced (no company), notify the instructor
        if (! $wasActivated && $student->needsCompanyAssignment()) {
            $instructorId = $student->assigned_instructor_id;
            if ($instructorId && ! session('needs_company_notified_' . $student->id)) {
                session(['needs_company_notified_' . $student->id => true]);
                Notification::route('mail', 'noop@example.com')
                    ->notify(new InAppNotification(
                        eventType: 'student.ready_no_company',
                        title: __('Student ready for placement'),
                        body: __(':name (:number) has completed all Pre documents but has no placement yet. Assign a company or change OJT type.', [
                            'name' => $student->name,
                            'number' => $student->student_number,
                        ]),
                        actionUrl: route('students.show', $student),
                    ));
                $instructor = \App\Models\User::find($instructorId);
                if ($instructor) {
                    $instructor->notify(new InAppNotification(
                        eventType: 'student.ready_no_company',
                        title: __('Student ready for placement'),
                        body: __(':name (:number) has completed all Pre documents but has no placement yet. Assign a company or change OJT type from their profile.', [
                            'name' => $student->name,
                            'number' => $student->student_number,
                        ]),
                        actionUrl: route('students.show', $student),
                    ));
                }
            }
        }

        $hasFullPortal = $student->hasFullStudentPortalAccess();
        $request->attributes->set('studentPortalLimited', ! $hasFullPortal);

        if (! $hasFullPortal) {
            $deploymentReady = $student->isDeploymentEligibleForPortal();
            $preApproved = $student->areAllPreDocsApproved();
            $portalBlockReason = match (true) {
                ! $deploymentReady && ! $preApproved => __('Full access is locked until instructor deployment and Pre-requirement document approvals are complete.'),
                ! $deploymentReady => __('Full access is locked until your instructor marks your deployment as active/completed.'),
                ! $preApproved => __('Full access is locked until all Pre-requirement documents are approved.'),
                default => __('Complete your requirements to unlock full access.'),
            };

            $allowed = [
                'student.documents',
                'student.documents.upload',
                'student.documents.download',
                'student.announcements',
                'student.certificates.index',
                'student.certificates.show',
                'student.certificates.download',
                'student.profile',
                'student.profile.update',
                'student.password.change',
                'student.password.update',
                'student.messages.index',
                'student.messages.create',
                'student.messages.store',
                'student.messages.show',
                'student.messages.reply',
                'student.messages.toggle-read',
                'student.messages.toggle-archive',
            ];
            $name = $request->route()?->getName();
            if ($name && ! in_array($name, $allowed, true)) {
                return redirect()
                    ->route('student.documents')
                    ->with('error', $portalBlockReason);
            }
        }

        if ($student->isDeploymentEligibleForPortal()) {
            $studentAccount->ensureAttendancePasscode();
        }

        $request->attributes->set('studentAccount', $studentAccount);
        $request->attributes->set('student', $student);

        $request->session()->put('student_last_activity', now()->timestamp);

        return $next($request);
    }
}
