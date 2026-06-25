<?php

namespace App\Http\Requests\Auth;

use App\Mail\StudentPortalOtpMail;
use App\Models\StudentAccount;
use App\Support\InternshipRoles;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required_if:role,'.implode(',', InternshipRoles::staffEmailRoles()), 'nullable', 'string', 'email', 'max:255'],
            'student_number' => ['required_if:role,student', 'nullable', 'digits:8'],
            'password' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:64', 'in:student,'.implode(',', InternshipRoles::staffEmailRoles())],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->email !== null ? trim((string) $this->email) : null;
        $rawSn = $this->student_number !== null ? preg_replace('/\D+/', '', (string) $this->student_number) : null;
        $studentNumber = ($rawSn !== null && $rawSn !== '') ? $rawSn : null;

        $this->merge([
            'email' => ($email === '' || $email === null) ? null : $email,
            'student_number' => $studentNumber,
        ]);
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();
        $selectedRole = $this->input('role');

        if ($selectedRole === 'student') {
            // Never allow staff and student portal sessions to coexist in one browser session.
            Auth::guard('web')->logout();

            $account = StudentAccount::query()
                ->with('student')
                ->whereHas('student', function ($query): void {
                    $query->where('student_number', (string) $this->input('student_number'));
                })
                ->first();

            if (! $account || ! $account->is_active || ! Hash::check((string) $this->input('password'), (string) $account->password)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    $this->credentialField() => trans('auth.failed'),
                ]);
            }

            $student = $account->student;
            if (! $student) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'student_number' => __('No student record is linked to this account. Please contact your coordinator.'),
                ]);
            }

            $email = trim((string) ($account->email ?? ''));
            if ($email === '') {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'student_number' => __('No email address is linked to your student account. Please contact your coordinator.'),
                ]);
            }

            // Trust this Browser — skip OTP if valid cookie exists
            $trustCookie = $this->cookie('student_trust_device', '');
            if ($trustCookie !== '') {
                try {
                    $trustData = json_decode(decrypt($trustCookie), true);
                    if (is_array($trustData) && isset($trustData['student_account_id']) && (int) $trustData['student_account_id'] === (int) $account->id) {
                        $this->session()->put('student_account_id', (int) $account->id);
                        $this->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at', 'student_otp_email']);
                        $account->forceFill(['last_login_at' => now()])->save();
                        RateLimiter::clear($this->throttleKey());
                        return;
                    }
                } catch (\Throwable) {
                    // Invalid cookie — fall through to normal OTP flow
                }
            }

            $otpCode = (string) random_int(100000, 999999);
            $this->session()->put('student_otp_pending_id', (int) $account->id);
            $this->session()->put('student_otp_code', $otpCode);
            $this->session()->put('student_otp_expires_at', now()->addMinutes(5)->toIso8601String());
            $this->session()->put('student_otp_email', $email);
            $this->session()->forget('student_account_id');
            RateLimiter::clear($this->throttleKey());

            $studentName = (string) ($student->name ?? $student->student_number ?? __('Student'));
            try {
                Mail::to($email)->send(new StudentPortalOtpMail($studentName, $otpCode));
            } catch (\Throwable $e) {
                Log::warning('student_otp_mail_failed', [
                    'student_account_id' => $account->id,
                    'message' => $e->getMessage(),
                ]);

                $this->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at', 'student_otp_email']);

                throw ValidationException::withMessages([
                    'student_number' => __('Unable to send OTP email. Please check your internet connection and try again, or contact your coordinator.'),
                ]);
            }

            return;
        }

        $this->session()->forget('student_account_id');
        $this->session()->forget(['student_otp_pending_id', 'student_otp_code', 'student_otp_expires_at']);

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $this->credentialField() => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // Allow only official portal roles.
        if (! in_array($user->role ?? 'student', array_merge(['student'], InternshipRoles::staffEmailRoles()), true)) {
            Auth::logout();

            throw ValidationException::withMessages([
                $this->credentialField() => __('You are not authorized to access this system. Please contact the system administrator.'),
            ]);
        }

        // Check that the user's actual role matches the role they selected on the login form
        if ($user->role !== $selectedRole) {
            $actualLabel = ucfirst($user->role);
            $selectedLabel = ucfirst($selectedRole);
            Auth::logout();

            throw ValidationException::withMessages([
                $this->credentialField() => __('This account is registered as :actual, not :selected. Please select the correct role and try again.', [
                    'actual' => $actualLabel,
                    'selected' => $selectedLabel,
                ]),
            ]);
        }

        // Force password change on first login for staff
        if ($user->isFirstLogin()) {
            session()->put('staff_password_change_required', true);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            $this->credentialField() => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->credentialValue()).'|'.$this->ip());
    }

    private function credentialField(): string
    {
        return $this->input('role') === 'student' ? 'student_number' : 'email';
    }

    private function credentialValue(): string
    {
        if ($this->input('role') === 'student') {
            return (string) $this->input('student_number', '');
        }

        return (string) $this->input('email', '');
    }
}
