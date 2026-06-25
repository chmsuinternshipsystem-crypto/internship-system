<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\StudentPortalOtpMail;
use App\Models\StudentAccount;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $studentAccountId = $request->session()->get('student_account_id');
        $studentOtpPendingId = $request->session()->get('student_otp_pending_id');
        $studentOtpCode = (string) $request->session()->get('student_otp_code', '');
        $studentOtpExpiresAt = (string) $request->session()->get('student_otp_expires_at', '');
        $studentOtpEmail = (string) $request->session()->get('student_otp_email', '');

        $request->session()->regenerate();

        // `regenerate()` can drop freshly written keys depending on driver timing; restore student marker.
        if ($studentAccountId) {
            $request->session()->put('student_account_id', (int) $studentAccountId);
            Auth::guard('web')->logout();
        }
        if ($studentOtpPendingId) {
            $request->session()->put('student_otp_pending_id', (int) $studentOtpPendingId);
            $request->session()->put('student_otp_code', $studentOtpCode);
            $request->session()->put('student_otp_expires_at', $studentOtpExpiresAt);
            $request->session()->put('student_otp_email', $studentOtpEmail);
            Auth::guard('web')->logout();
        }

        $defaultRoute = $request->session()->has('student_account_id')
            ? route('student.dashboard', absolute: false)
            : ($request->session()->has('student_otp_pending_id')
                ? route('student.otp.show', absolute: false)
                : route('dashboard', absolute: false));

        // Redirect staff with first_login flag to password change page
        if ($request->session()->pull('staff_password_change_required', false)) {
            return redirect()->route('staff.password.change');
        }

        // Do not use `intended()` for student portal logins: a prior staff login may have stored `/dashboard`
        // as the "intended" URL and would incorrectly send students to the staff dashboard.
        if ($request->session()->has('student_account_id')) {
            return redirect()->to($defaultRoute);
        }
        if ($request->session()->has('student_otp_pending_id')) {
            return redirect()->to($defaultRoute);
        }

        return redirect()->intended($defaultRoute);
    }

    public function showStudentOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('student_otp_pending_id')) {
            return redirect()->route('login');
        }

        return view('auth.student-otp');
    }

    public function verifyStudentOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingId = (int) $request->session()->get('student_otp_pending_id', 0);
        $expectedOtp = (string) $request->session()->get('student_otp_code', '');
        $expiresAt = (string) $request->session()->get('student_otp_expires_at', '');

        if ($pendingId <= 0 || $expectedOtp === '' || $expiresAt === '') {
            return redirect()->route('login')->with('error', __('OTP session expired. Please sign in again.'));
        }

        if (Carbon::parse($expiresAt)->isPast()) {
            $request->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at']);

            return redirect()->route('login')->with('error', __('OTP has expired. Please sign in again.'));
        }

        if (! hash_equals($expectedOtp, (string) $request->input('otp'))) {
            return back()->withErrors([
                'otp' => __('Invalid OTP code.'),
            ]);
        }

        $account = StudentAccount::query()->with('student')->find($pendingId);
        if (! $account || ! $account->is_active || ! $account->student) {
            $request->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at']);

            return redirect()->route('login')->withErrors([
                'student_number' => __('Your student account is not available. Please contact your coordinator.'),
            ]);
        }

        $request->session()->put('student_account_id', $account->id);
        $request->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at', 'student_otp_email']);

        $student = $account->student;

        if ($request->boolean('trust_this_browser')) {
            $cookieValue = encrypt(json_encode([
                'student_account_id' => $account->id,
                'trusted_at' => now()->toIso8601String(),
            ]));
            $account->forceFill(['last_login_at' => now()])->save();

            $home = $student->hasFullStudentPortalAccess() ? 'student.dashboard' : 'student.documents';

            if ($account->isFirstLogin()) {
                return redirect()->route('student.password.change')
                    ->cookie('student_trust_device', $cookieValue, 60 * 24 * 30, '/', null, true, true);
            }

            return redirect()->route($home)
                ->cookie('student_trust_device', $cookieValue, 60 * 24 * 30, '/', null, true, true);
        }

        $account->forceFill(['last_login_at' => now()])->save();

        // Check if first login - redirect to password change
        if ($account->isFirstLogin()) {
            return redirect()->route('student.password.change');
        }

        $home = $student->hasFullStudentPortalAccess() ? 'student.dashboard' : 'student.documents';

        return redirect()->route($home);
    }

    public function resendStudentOtp(Request $request): RedirectResponse
    {
        $pendingId = (int) $request->session()->get('student_otp_pending_id', 0);
        $expiresAt = (string) $request->session()->get('student_otp_expires_at', '');

        if ($pendingId <= 0 || $expiresAt === '') {
            return redirect()->route('login')->with('error', __('OTP session expired. Please sign in again.'));
        }

        $throttleKey = 'otp-resend:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'otp' => __('Please wait :seconds seconds before requesting a new code.', ['seconds' => $seconds]),
            ]);
        }

        $account = StudentAccount::query()->with('student')->find($pendingId);
        if (! $account || ! $account->is_active || ! $account->student) {
            $request->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at', 'student_otp_email']);

            return redirect()->route('login')->with('error', __('Your student account is not available. Please contact your coordinator.'));
        }

        $email = trim((string) ($account->email ?? ''));
        if ($email === '') {
            return redirect()->route('login')->with('error', __('No email address is linked to your student account. Please contact your coordinator.'));
        }

        $otpCode = (string) random_int(100000, 999999);
        $request->session()->put('student_otp_code', $otpCode);
        $request->session()->put('student_otp_expires_at', now()->addMinutes(5)->toIso8601String());
        $request->session()->put('student_otp_email', $email);
        $request->session()->forget('student_account_id');

        RateLimiter::hit($throttleKey, 60);

        $studentName = (string) ($account->student->name ?? $account->student->student_number ?? __('Student'));
        try {
            Mail::to($email)->send(new StudentPortalOtpMail($studentName, $otpCode));
        } catch (\Throwable $e) {
            Log::warning('student_otp_resend_mail_failed', [
                'student_account_id' => $account->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'otp' => __('Unable to resend OTP email. Please check your internet connection and try again, or contact your coordinator.'),
            ]);
        }

        return redirect()->route('student.otp.show')->with('status', __('A new code has been sent to your email.'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->forget(['student_account_id', 'student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at', 'student_otp_email']);

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
