<?php

namespace Tests\Feature\Auth;

use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['first_login' => false]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => $user->role,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => $user->role,
        ]);

        $this->assertGuest();
    }

    public function test_students_can_authenticate_using_student_number(): void
    {
        $student = Student::create([
            'user_id' => null,
            'name' => 'Student One',
            'student_number' => '20230001',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);
        $account = StudentAccount::create([
            'student_id' => $student->id,
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'student_number' => $student->student_number,
            'password' => 'password',
            'role' => 'student',
        ]);

        $this->assertFalse(auth()->check());
        $this->assertSame($account->id, session('student_otp_pending_id'));
        $response->assertRedirect(route('student.otp.show', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_staff_login_clears_student_portal_session_marker(): void
    {
        $user = User::factory()->create(['role' => 'chairperson', 'first_login' => false]);

        $response = $this->withSession(['student_account_id' => 999])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'chairperson',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(session()->has('student_account_id'));
    }

    public function test_student_login_after_staff_login_sets_student_session_and_redirects(): void
    {
        $chair = User::factory()->create(['role' => 'chairperson', 'first_login' => false]);
        $this->post('/login', [
            'email' => $chair->email,
            'password' => 'password',
            'role' => 'chairperson',
        ]);
        $this->assertAuthenticatedAs($chair);

        $student = Student::create([
            'user_id' => null,
            'name' => 'Student One',
            'student_number' => '20230001',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'deployed',
        ]);
        $account = StudentAccount::create([
            'student_id' => $student->id,
            'email' => 'student@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'student_number' => '20230001',
            'password' => 'password',
            'role' => 'student',
        ]);

        $response->assertRedirect(route('student.otp.show', absolute: false));
        $response->assertSessionHas('student_otp_pending_id', $account->id);
    }

    public function test_home_route_prefers_staff_session_over_stale_student_marker(): void
    {
        $chair = User::factory()->create(['role' => 'chairperson']);

        $response = $this->actingAs($chair)->withSession(['student_account_id' => 1])->get('/');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertFalse(session()->has('student_account_id'));
    }
}
