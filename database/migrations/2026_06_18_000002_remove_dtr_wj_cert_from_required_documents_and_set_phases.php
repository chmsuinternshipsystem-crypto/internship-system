<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const REMOVE = [
        'Weekly Journal',
        'Daily Time Record',
        'Certificate of Completion',
    ];

    private const PRE_DEPLOYMENT = [
        'Enrolment Form',
        'NBI Clearance',
        'Pledge of Good Conduct',
        'Memorandum of Agreement',
        'Internship Agreement',
        'Medical Certificate',
        'Parent Consent Form',
        'Application Letter',
        'Resume',
        'Endorsement Letter',
        'Acceptance Letter',
        'Training Plan',
    ];

    private const MONITORING = [
        'OJT Supervisor Evaluation Form',
    ];

    public function up(): void
    {
        // Remove DTR, Weekly Journal, Certificate of Completion
        RequiredDocument::query()
            ->whereIn('name', self::REMOVE)
            ->delete();

        // Set phase for pre-deployment documents
        RequiredDocument::query()
            ->whereIn('name', self::PRE_DEPLOYMENT)
            ->update(['phase' => 'pre']);

        // Set phase for monitoring documents
        RequiredDocument::query()
            ->whereIn('name', self::MONITORING)
            ->update(['phase' => 'monitoring']);
    }

    public function down(): void
    {
        // Re-set all phases back to 'all'
        RequiredDocument::query()
            ->whereIn('name', self::PRE_DEPLOYMENT)
            ->update(['phase' => 'all']);

        RequiredDocument::query()
            ->whereIn('name', self::MONITORING)
            ->update(['phase' => 'all']);

        // We cannot restore deleted records in down().
    }
};
