<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupUsersCommand extends Command
{
    protected $signature = 'users:cleanup-students
                            {--apply : Delete matched users instead of reporting only}
                            {--id=* : Limit operation to specific user IDs}
                            {--force : Allow deleting users with linked operational records}';

    protected $description = 'Dry-run and cleanup unnecessary student users not linked to student profiles.';

    public function handle(): int
    {
        $ids = collect($this->option('id'))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        $query = User::query()
            ->where('role', 'student')
            ->whereDoesntHave('student')
            ->orderBy('id');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids->all());
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info('No unnecessary student users found (role=student with no linked student profile).');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($users as $user) {
            $impacts = $this->impactCounts((int) $user->id);
            $impactTotal = array_sum($impacts);
            $rows[] = [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'impact' => $impactTotal,
                'reason' => 'student user has no linked students.user_id',
            ];
        }

        $this->table(['ID', 'Email', 'Role', 'Linked Records', 'Reason'], $rows);
        $this->line('Dry-run complete. Use --apply to delete listed users.');

        if (! $this->option('apply')) {
            return self::SUCCESS;
        }

        if (! $this->confirm('Proceed with deleting listed users?', false)) {
            $this->warn('Cleanup cancelled.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $impacts = $this->impactCounts((int) $user->id);
            $impactTotal = array_sum($impacts);

            if ($impactTotal > 0 && ! $this->option('force')) {
                $this->warn("Skipped user {$user->id} ({$user->email}) because linked records exist. Use --force to allow.");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($user): void {
                $user->delete();
            });

            $deleted++;
        }

        $this->info("Cleanup finished. Deleted: {$deleted}. Skipped: {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string,int>
     */
    private function impactCounts(int $userId): array
    {
        return [
            'sessions' => DB::table('sessions')->where('user_id', $userId)->count(),
            'message_threads' => DB::table('message_threads')->where('created_by', $userId)->count(),
            'message_participants' => DB::table('message_thread_participants')->where('user_id', $userId)->count(),
            'messages' => DB::table('messages')->where('sender_id', $userId)->count(),
            'evaluations' => DB::table('evaluations')->where('evaluator_id', $userId)->count(),
            'announcements' => DB::table('announcements')->where('created_by', $userId)->count(),
            'remarks' => DB::table('remarks')->where('author_id', $userId)->count(),
            'student_documents_uploaded' => DB::table('student_documents')->where('uploaded_by', $userId)->count(),
            'student_documents_verified' => DB::table('student_documents')->where('verified_by', $userId)->count(),
        ];
    }
}
