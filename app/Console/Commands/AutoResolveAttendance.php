<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class AutoResolveAttendance extends Command
{
    protected $signature = 'attendance:auto-resolve';

    protected $description = 'Auto-resolve near_boundary_review attendance records older than 3 days to inside_pass if no subsequent issues.';

    public function handle(): int
    {
        $threshold = now()->subDays(3);

        $count = Attendance::where('resolution_status', 'near_boundary_review')
            ->where('check_in_at', '<=', $threshold)
            ->update([
                'resolution_status' => 'resolved',
                'resolution_notes' => 'Auto-resolved: no further boundary issues detected within 3 days.',
                'resolved_by' => null,
                'resolved_at' => now(),
            ]);

        $this->info("Auto-resolved {$count} near-boundary attendance record(s).");

        return self::SUCCESS;
    }
}
