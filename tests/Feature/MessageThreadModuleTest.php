<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageThreadModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_can_create_message_thread_and_initial_message(): void
    {
        $sender = User::factory()->create(['role' => 'instructor']);
        $recipient = User::factory()->create(['role' => 'chairperson']);

        $response = $this->actingAs($sender)->post(route('messages.store'), [
            'subject' => 'Deployment coordination',
            'participant_ids' => [$recipient->id],
            'body' => 'Please confirm available internship slots this week.',
        ]);

        $response->assertRedirect();

        $thread = MessageThread::query()->first();
        $this->assertNotNull($thread);
        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_id' => $sender->id,
        ]);
        $this->assertDatabaseHas('message_thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
        ]);
        $this->assertDatabaseHas('message_thread_participants', [
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
        ]);
    }

    public function test_participant_can_view_and_reply_to_thread(): void
    {
        $sender = User::factory()->create(['role' => 'instructor']);
        $recipient = User::factory()->create(['role' => 'chairperson']);

        $thread = MessageThread::query()->create([
            'subject' => 'Compliance follow-up',
            'created_by' => $sender->id,
            'created_by_student_account_id' => null,
        ]);

        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'student_account_id' => null,
            'last_read_at' => now(),
        ]);
        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
            'student_account_id' => null,
            'last_read_at' => null,
        ]);
        $thread->messages()->create([
            'sender_id' => $sender->id,
            'sender_student_account_id' => null,
            'body' => 'Please review the pending student documents.',
        ]);

        $this->actingAs($recipient)
            ->get(route('messages.show', $thread))
            ->assertOk();

        $this->actingAs($recipient)
            ->post(route('messages.reply', $thread), ['body' => 'Acknowledged. I will review today.'])
            ->assertRedirect(route('messages.show', $thread));

        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_id' => $recipient->id,
            'body' => 'Acknowledged. I will review today.',
        ]);
    }

    public function test_non_participant_cannot_view_message_thread(): void
    {
        $sender = User::factory()->create(['role' => 'instructor']);
        $recipient = User::factory()->create(['role' => 'chairperson']);
        $outsider = User::factory()->create(['role' => 'dean']);

        $thread = MessageThread::query()->create([
            'subject' => 'Evaluation reminder',
            'created_by' => $sender->id,
            'created_by_student_account_id' => null,
        ]);
        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $sender->id,
            'student_account_id' => null,
            'last_read_at' => null,
        ]);
        MessageThreadParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $recipient->id,
            'student_account_id' => null,
            'last_read_at' => null,
        ]);
        $thread->messages()->create([
            'sender_id' => $sender->id,
            'sender_student_account_id' => null,
            'body' => 'Please submit the latest evaluation reports.',
        ]);

        $this->actingAs($outsider)
            ->get(route('messages.show', $thread))
            ->assertForbidden();
    }

    public function test_student_portal_can_create_thread_to_instructor(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = Student::create([
            'user_id' => null,
            'last_name' => 'Messaging',
            'first_name' => 'Student',
            'middle_name' => null,
            'name_extension' => null,
            'student_number' => '20230099',
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
        ]);

        Deployment::create([
            'student_id' => $student->id,
            'company_id' => $company->id,
            'start_date' => now()->subDays(1)->toDateString(),
            'end_date' => now()->addDays(40)->toDateString(),
            'status' => 'active',
        ]);

        $account = StudentAccount::create([
            'student_id' => $student->id,
            'email' => null,
            'password' => bcrypt('password'),
            'is_active' => true,
            'attendance_passcode' => '123456',
            'attendance_passcode_generated_at' => now(),
            'first_login' => false,
        ]);

        $response = $this->withSession(['student_account_id' => $account->id])
            ->post(route('student.messages.store'), [
                'subject' => 'Question about OJT',
                'participant_ids' => [$instructor->id],
                'body' => 'Hello, I have a question about my deployment.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('message_threads', [
            'created_by' => null,
            'created_by_student_account_id' => $account->id,
        ]);

        $thread = MessageThread::query()->first();
        $this->assertNotNull($thread);
        $this->assertDatabaseHas('messages', [
            'thread_id' => $thread->id,
            'sender_student_account_id' => $account->id,
            'sender_id' => null,
        ]);
    }
}
