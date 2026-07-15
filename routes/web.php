<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CampusSettingController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyImportController;
use App\Http\Controllers\CompanyIndustryController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DocumentForwardingController;
use App\Http\Controllers\DtrController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationCriterionController;
use App\Http\Controllers\HteTransactionController;
use App\Http\Controllers\MessageThreadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RemarkController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequiredDocumentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WeeklyJournalController;
use App\Models\StudentAccount;
use App\Support\InternshipRoles;
use Illuminate\Support\Facades\Route;

// Root URL: send logged-in users to dashboard, others to login
Route::get('/', function () {
    if (auth()->check()) {
        // Staff/employer sessions take priority; drop stale student portal marker (prevents cross-role access).
        if (session()->has('student_account_id')) {
            session()->forget('student_account_id');
        }

        $target = auth()->user()?->role === 'student' ? 'student.dashboard' : 'dashboard';

        return redirect()->route($target);
    }

    if (session()->has('student_account_id')) {
        $account = StudentAccount::query()->with('student')->find((int) session('student_account_id'));
        if ($account && $account->student && ! $account->student->hasFullStudentPortalAccess()) {
            return redirect()->route('student.documents');
        }

        return redirect()->route('student.dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::view('/maintenance', 'errors.maintenance')->name('maintenance');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'role:'.implode(',', InternshipRoles::dashboardRoles())])
    ->name('dashboard');

// Public/student attendance check-in (geofencing)
Route::get('/attendance/check-in', [AttendanceController::class, 'create'])
    ->name('attendance.check-in');
Route::post('/attendance/check-in', [AttendanceController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('attendance.store');

// External HTE transaction access (no login required; one-time token link)
Route::get('/hte/transaction/{token}', [HteTransactionController::class, 'show'])
    ->name('hte.transaction.show');
Route::post('/hte/transaction/{token}/evaluate', [HteTransactionController::class, 'submitEvaluation'])
    ->middleware('throttle:20,1')
    ->name('hte.transaction.evaluate');
Route::post('/hte/transaction/{token}/upload', [HteTransactionController::class, 'submitUpload'])
    ->middleware('throttle:20,1')
    ->name('hte.transaction.upload');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Literal `*/create` routes must be registered before `resource/{id}` routes or "create" is captured as an ID.
    Route::middleware('role:instructor')->group(function () {
        Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
        Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::get('required-documents/create', [RequiredDocumentController::class, 'create'])->name('required-documents.create');
        Route::get('evaluations/create', [EvaluationController::class, 'create'])->name('evaluations.create');
        Route::get('evaluations/send-hte-link', [EvaluationController::class, 'createHteLink'])->name('evaluations.hte-links.create');
    });

    Route::middleware('role:instructor,chairperson')->group(function () {
        Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    });

    // Staff read access: companies, messaging, announcements, per-student document workflow.
    Route::middleware('role:'.implode(',', InternshipRoles::staffPortalReadRoles()))->group(function () {
        // Literal routes before wildcard {company} — instructor only
        Route::middleware('role:instructor')->group(function () {
            Route::get('companies/import', [CompanyImportController::class, 'create'])->name('companies.import');
            Route::post('companies/import', [CompanyImportController::class, 'store'])->name('companies.import.store');
        });

        Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');

        Route::get('students/{student}/documents', [StudentDocumentController::class, 'edit'])->name('student-documents.edit');
        Route::get('students/{student}/documents/{studentDocument}/download', [StudentDocumentController::class, 'download'])
            ->name('student-documents.download');
        Route::post('students/{student}/documents/{studentDocument}/workflow-action', [StudentDocumentController::class, 'workflowAction'])
            ->name('student-documents.workflow-action');
        Route::post('students/{student}/documents/{studentDocument}/review', [StudentDocumentController::class, 'review'])
            ->name('student-documents.review');
        Route::get('students/{student}/documents/{requiredDocument}/upload-panel', [StudentDocumentController::class, 'uploadPanel'])
            ->name('student-documents.upload-panel');
        Route::get('students/{student}/documents/{studentDocument}/preview', [StudentDocumentController::class, 'preview'])
            ->name('student-documents.preview');
        Route::get('workflow/queue', [StudentDocumentController::class, 'queue'])->name('student-documents.queue');

        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

        Route::get('messages', [MessageThreadController::class, 'index'])->name('messages.index');
        Route::get('messages/create', [MessageThreadController::class, 'create'])->name('messages.create');
        Route::post('messages', [MessageThreadController::class, 'store'])->name('messages.store');
        Route::get('messages/{message}', [MessageThreadController::class, 'show'])->name('messages.show');
        Route::post('messages/{message}/reply', [MessageThreadController::class, 'reply'])->name('messages.reply');
        Route::post('messages/{message}/toggle-read', [MessageThreadController::class, 'toggleRead'])->name('messages.toggle-read');
        Route::post('messages/{message}/toggle-archive', [MessageThreadController::class, 'toggleArchive'])->name('messages.toggle-archive');
        Route::get('document-forwarding', [DocumentForwardingController::class, 'index'])->name('document-forwarding.index');
    });

    // Staff first login password change
    Route::middleware('auth')->group(function () {
        Route::get('/password/change', [ProfileController::class, 'showStaffPasswordChange'])->name('staff.password.change');
        Route::post('/password/change', [ProfileController::class, 'updateStaffPassword'])->name('staff.password.update');
    });

    // School-wide student registry and institutional monitoring (not employers).
    Route::middleware('role:'.implode(',', InternshipRoles::institutionalMonitoringRoles()))->group(function () {
        // Literal routes before wildcard {student} — instructor only
        Route::middleware('role:instructor')->group(function () {
            Route::get('students/import', [StudentImportController::class, 'create'])->name('students.import');
            Route::post('students/import', [StudentImportController::class, 'store'])->name('students.import.store');
        });

        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('students/check-duplicate-name', [StudentController::class, 'checkDuplicateName'])->name('students.check-duplicate-name');
        Route::get('students/{student}/tab/journals', [StudentController::class, 'tabJournals'])->name('students.tab.journals');
        Route::get('students/{student}/tab/dtr', [StudentController::class, 'tabDtr'])->name('students.tab.dtr');
        Route::get('students/{student}/tab/attendance', [StudentController::class, 'tabAttendance'])->name('students.tab.attendance');
        Route::get('students/{student}/tab/certificates', [StudentController::class, 'tabCertificates'])->name('students.tab.certificates');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show');

        Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance.index');

        Route::get('evaluations/criteria', [EvaluationCriterionController::class, 'index'])->name('evaluations.criteria.index');
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('evaluations.show');
        Route::get('evaluations/{evaluation}/export', [EvaluationController::class, 'export'])->name('evaluations.export.docx');

        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        Route::middleware('role:instructor,chairperson')->group(function () {
            Route::post('attendance/{attendance}/resolve', [AttendanceController::class, 'resolve'])->name('attendance.resolve');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/deployed-per-company', [ReportController::class, 'deployedPerCompany'])->name('deployed-per-company');
            Route::get('/missing-documents', [ReportController::class, 'missingDocuments'])->name('missing-documents');
            Route::get('/compliance-summary', [ReportController::class, 'complianceSummary'])->name('compliance-summary');
            Route::get('/deployment-locations', [ReportController::class, 'deploymentLocations'])->name('deployment-locations');
            Route::get('/attendance-export', [ReportController::class, 'attendanceExport'])->name('attendance-export');

            Route::get('/executive-summary', [ReportController::class, 'executiveSummary'])->name('executive-summary')->middleware('role:dean');
        });
    });

    // Master document catalog (program + CIER/Legal); Dean/VPAA use Compliance for oversight.
    Route::middleware('role:'.implode(',', InternshipRoles::requiredDocumentCatalogRoles()))->group(function () {
        Route::get('required-documents', [RequiredDocumentController::class, 'index'])->name('required-documents.index');
        Route::get('required-documents/{required_document}', [RequiredDocumentController::class, 'show'])->name('required-documents.show');
    });

    // Write access restricted to instructor (admin) only
    Route::middleware('role:instructor')->group(function () {
        // Students
        Route::post('students', [StudentController::class, 'store'])->name('students.store');
        Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        // Company management
        Route::get('companies/geocode', [CompanyController::class, 'geocode'])->name('companies.geocode');
        Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
        Route::get('companies/{company}/students/assignable', [CompanyController::class, 'assignableStudents'])->name('companies.students.assignable');
        Route::post('companies/{company}/students/attach', [CompanyController::class, 'attachStudents'])->name('companies.students.attach');
        Route::post('companies/{company}/students/detach', [CompanyController::class, 'detachStudent'])->name('companies.students.detach');

        // Company industries
        Route::get('company-industries', [CompanyIndustryController::class, 'index'])->name('company-industries.index');
        Route::get('company-industries/create', [CompanyIndustryController::class, 'create'])->name('company-industries.create');
        Route::post('company-industries', [CompanyIndustryController::class, 'store'])->name('company-industries.store');
        Route::get('company-industries/{company_industry}/edit', [CompanyIndustryController::class, 'edit'])->name('company-industries.edit');
        Route::put('company-industries/{company_industry}', [CompanyIndustryController::class, 'update'])->name('company-industries.update');

        // Deployment management (link students to companies)
        Route::get('deployments/create', [DeploymentController::class, 'create'])->name('deployments.create');
        Route::post('deployments', [DeploymentController::class, 'store'])->name('deployments.store');
        Route::get('deployments/{deployment}/edit', [DeploymentController::class, 'edit'])->name('deployments.edit');
        Route::put('deployments/{deployment}', [DeploymentController::class, 'update'])->name('deployments.update');
        Route::post('deployments/{deployment}/assign-company', [DeploymentController::class, 'assignCompany'])->name('deployments.assign-company');
        Route::delete('deployments/{deployment}', [DeploymentController::class, 'destroy'])->name('deployments.destroy');

        // Required documents management (master list of internship documents)
        Route::post('required-documents', [RequiredDocumentController::class, 'store'])->name('required-documents.store');
        Route::get('required-documents/{required_document}/edit', [RequiredDocumentController::class, 'edit'])->name('required-documents.edit');
        Route::put('required-documents/{required_document}', [RequiredDocumentController::class, 'update'])->name('required-documents.update');
        Route::delete('required-documents/{required_document}', [RequiredDocumentController::class, 'destroy'])->name('required-documents.destroy');


        // Per-student document compliance checklist (write)
        Route::post('students/{student}/documents', [StudentDocumentController::class, 'update'])->name('student-documents.update');

        // Evaluations: performance evaluation records per student
        Route::post('evaluations/criteria', [EvaluationCriterionController::class, 'store'])->name('evaluations.criteria.store');
        Route::delete('evaluations/criteria/{criterion}', [EvaluationCriterionController::class, 'destroy'])->name('evaluations.criteria.destroy');
        Route::post('evaluations', [EvaluationController::class, 'store'])->name('evaluations.store');
        Route::post('evaluations/send-hte-link', [EvaluationController::class, 'storeHteLink'])->name('evaluations.hte-links.store');
        Route::get('evaluations/{evaluation}/edit', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::put('evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('evaluations.update');
        Route::delete('evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('evaluations.destroy');

        // Remarks: per-student internal remarks
        Route::post('students/{student}/remarks', [RemarkController::class, 'store'])->name('students.remarks.store');

        // Campus settings (geofencing configuration)
        Route::get('settings/campus', [CampusSettingController::class, 'edit'])->name('settings.campus.edit');
        Route::put('settings/campus', [CampusSettingController::class, 'update'])->name('settings.campus.update');

        // Batch transmittal scheduler and receipt tracking
        Route::get('document-forwarding/create', [DocumentForwardingController::class, 'create'])->name('document-forwarding.create');
        Route::post('document-forwarding', [DocumentForwardingController::class, 'store'])->name('document-forwarding.store');
        Route::post('document-forwarding/{batch}/release', [DocumentForwardingController::class, 'release'])->name('document-forwarding.release');
        Route::post('document-forwarding/items/{item}/acknowledge', [DocumentForwardingController::class, 'acknowledge'])->name('document-forwarding.acknowledge');

        // Weekly Journals - Instructor only
        Route::get('weekly-journals', [WeeklyJournalController::class, 'index'])->name('weekly-journals.index');
        Route::get('weekly-journals/student/{student}', [WeeklyJournalController::class, 'studentProgress'])->name('weekly-journals.student');
        Route::get('weekly-journals/{weeklyJournal}', [WeeklyJournalController::class, 'show'])->name('weekly-journals.show');
        Route::post('weekly-journals/{weeklyJournal}/review', [WeeklyJournalController::class, 'review'])->name('weekly-journals.review');
        Route::get('weekly-journals/{weeklyJournal}/file/{day}', [WeeklyJournalController::class, 'downloadFile'])->name('weekly-journals.file');

        // Daily Time Records - Instructor only
        Route::get('dtr', [DtrController::class, 'index'])->name('dtr.index');
        Route::get('dtr/{dtr}', [DtrController::class, 'show'])->name('dtr.show');

        // Batch operations
        Route::prefix('batch')->name('batch.')->group(function () {
            Route::post('journals', [BatchController::class, 'journals'])->name('journals');
            Route::post('students', [BatchController::class, 'students'])->name('students');
            Route::post('attendance', [BatchController::class, 'attendance'])->name('attendance');
            Route::post('certificates', [BatchController::class, 'certificates'])->name('certificates');
        });

        // Certificates - Instructor only
        Route::get('certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('certificates/create', [CertificateController::class, 'create'])->name('certificates.create');
        Route::post('certificates', [CertificateController::class, 'store'])->name('certificates.store');
        Route::get('certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
        Route::post('certificates/{certificate}/verify', [CertificateController::class, 'verify'])->name('certificates.verify');
        Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

    // Announcements: instructors and chairpersons may post and maintain official notices.
    Route::middleware('role:instructor,chairperson')->group(function () {
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
        Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // Deployment read access: instructor / chairperson / dean
    Route::middleware('role:'.implode(',', InternshipRoles::deploymentViewerRoles()))->group(function () {
        Route::get('deployments', [DeploymentController::class, 'index'])->name('deployments.index');
        Route::get('deployments/{deployment}', [DeploymentController::class, 'show'])->name('deployments.show');
    });

});

// Student portal routes with dedicated student account session middleware
Route::middleware('student.auth')->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/portal-access-status', [StudentPortalController::class, 'portalAccessStatus'])->name('portal-access-status');
    Route::get('/documents', [StudentPortalController::class, 'documents'])->name('documents');
    Route::post('/documents/{requiredDocument}/upload', [StudentPortalController::class, 'uploadDocument'])->name('documents.upload');
    Route::get('/documents/file/{studentDocument}', [StudentPortalController::class, 'downloadDocument'])->name('documents.download');
    Route::get('/announcements', [StudentPortalController::class, 'announcements'])->name('announcements');
    // First login password change
    Route::get('/password/change', [StudentPortalController::class, 'showPasswordChange'])->name('password.change');
    Route::post('/password/change', [StudentPortalController::class, 'updatePassword'])->name('password.update');

    // Profile & settings
    Route::get('/profile', [StudentPortalController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [StudentPortalController::class, 'updateProfile'])->name('profile.update');

    // Weekly Journals — single-page AJAX editor
    Route::get('/weekly-journals', [WeeklyJournalController::class, 'studentIndex'])->name('weekly-journals.index');
    Route::get('/weekly-journals/{weeklyJournal}', [WeeklyJournalController::class, 'studentShow'])->name('weekly-journals.show');
    Route::patch('/weekly-journals/{weeklyJournal}/activities', [WeeklyJournalController::class, 'updateActivities'])->name('weekly-journals.activities');
    Route::post('/weekly-journals/{weeklyJournal}/files', [WeeklyJournalController::class, 'uploadFile'])->name('weekly-journals.files.upload');
    Route::delete('/weekly-journals/{weeklyJournal}/files', [WeeklyJournalController::class, 'deleteFile'])->name('weekly-journals.files.delete');
    Route::post('/weekly-journals/{weeklyJournal}/submit', [WeeklyJournalController::class, 'studentSubmit'])->name('weekly-journals.submit');
    Route::get('/weekly-journals/{weeklyJournal}/export', [WeeklyJournalController::class, 'exportDocx'])->name('weekly-journals.export');
    Route::get('/weekly-journals/{weeklyJournal}/file/{day}', [WeeklyJournalController::class, 'downloadFile'])->name('weekly-journals.file');

    // Daily Time Records (auto-populated from attendance)
    Route::get('/dtr', [DtrController::class, 'studentIndex'])->name('dtr.index');
    Route::get('/dtr/monthly/export', [DtrController::class, 'exportDttrData'])->name('dtr.export');
    Route::post('/dtr/monthly/upload-signed', [DtrController::class, 'uploadSignedDttr'])->name('dtr.upload-signed');
    Route::post('/dtr/tasks', [DtrController::class, 'updateTasks'])->name('dtr.tasks');
    Route::get('/dtr/{dtr}', [DtrController::class, 'studentShow'])->name('dtr.show');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'studentIndex'])->name('certificates.index');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'studentShow'])->name('certificates.show');
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

    // HTE Evaluation — download completed evaluation form
    Route::get('/evaluations/{evaluation}/export', [StudentPortalController::class, 'exportEvaluation'])->name('evaluations.export');

    // Student ↔ staff messaging (uses student_accounts; same controller as staff with MessageActor)
    Route::get('/messages', [MessageThreadController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessageThreadController::class, 'create'])->name('messages.create');
    Route::post('/messages', [MessageThreadController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageThreadController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/reply', [MessageThreadController::class, 'reply'])->name('messages.reply');
    Route::post('/messages/{message}/toggle-read', [MessageThreadController::class, 'toggleRead'])->name('messages.toggle-read');
    Route::post('/messages/{message}/toggle-archive', [MessageThreadController::class, 'toggleArchive'])->name('messages.toggle-archive');

    // Quick Clock-In/Out from dashboard (session-authenticated, no passcode needed)
    Route::post('/attendance/quick-clock', [AttendanceController::class, 'quickClock'])->name('attendance.quick-clock');
});

// Student logout — outside student.auth middleware (user is already logged out)
Route::post('/student/logout', [\App\Http\Controllers\StudentPortalController::class, 'logout'])
    ->name('student.logout');

// Undo routes (session-authenticated, uses cache with 30s TTL)
Route::post('/undo/{key}', [\App\Http\Controllers\UndoController::class, 'restore'])
    ->name('undo.restore');

// Philippine address cascading dropdown (used by staff company forms and student portal)
Route::get('address/provinces', [\App\Http\Controllers\AddressController::class, 'provinces'])->name('address.provinces');
Route::get('address/cities/{province}', [\App\Http\Controllers\AddressController::class, 'cities'])->name('address.cities');
Route::get('address/barangays/{city}', [\App\Http\Controllers\AddressController::class, 'barangays'])->name('address.barangays');

// In-app notifications for all authenticated users (staff + students; controller resolves notifiable)
Route::prefix('notifications')->name('notifications.')->middleware('web')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
});

require __DIR__.'/auth.php';








































