<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanupUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_command_reports_unlinked_student_users_in_dry_run(): void
    {
        $orphan = User::factory()->create([
            'role' => 'student',
            'email' => 'orphan.student@example.com',
        ]);

        $linkedUser = User::factory()->create([
            'role' => 'student',
            'email' => 'linked.student@example.com',
        ]);

        Student::create([
            'user_id' => $linkedUser->id,
            'name' => 'Linked Student',
            'student_number' => '20239999',
            'program' => 'BSIS',
            'year_level' => 4,
            'section' => 'A',
            'status' => 'pending',
        ]);

        $this->artisan('users:cleanup-students')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $orphan->id]);
        $this->assertDatabaseHas('users', ['id' => $linkedUser->id]);
    }

    public function test_cleanup_command_can_delete_unlinked_student_users_when_apply_is_confirmed(): void
    {
        $orphan = User::factory()->create([
            'role' => 'student',
            'email' => 'delete.me@example.com',
        ]);

        $this->artisan('users:cleanup-students --apply')
            ->expectsConfirmation('Proceed with deleting listed users?', 'yes')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $orphan->id]);
    }
}
