<nav x-data="{ open: false }" class="md:h-full">
    @php
        $staffUser = auth()->user();
        $role = $staffUser?->role ?? (session()->has('student_account_id') ? 'student' : '');
        $isStudentRoute = request()->is('student/*') || request()->routeIs('attendance.check-in');
        $isStudentSession = $role === 'student';

        if ($role === 'student') {
            $studentAccountId = (int) session('student_account_id', 0);
            $studentAccount = $studentAccountId > 0 ? \App\Models\StudentAccount::with('student')->find($studentAccountId) : null;
            $hasFullAccess = $studentAccount?->student?->hasFullStudentPortalAccess() ?? false;
            $studentPortalLimited = !$hasFullAccess;
            $homeRoute = route($hasFullAccess ? 'student.dashboard' : 'student.documents');
            $displayName = $studentAccount?->student?->name ?? $staffUser?->name ?? '';
            $displayEmail = $studentAccount?->email ?? $staffUser?->email ?? '';
        } else {
            $homeRoute = route('dashboard');
            $displayName = $staffUser?->name ?? '';
            $displayEmail = $staffUser?->email ?? '';
        }

        $showDashboardNav = $role && $role !== 'student' && \App\Support\InternshipRoles::staffSidebarShows($role, 'dashboard');
        $canViewStudents = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::studentRegistryRoles(), true);
        $canViewDeployments = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::deploymentViewerRoles(), true);
        $canViewCompanies = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::programAdministratorRoles(), true);
        $canViewCompliance = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::institutionalMonitoringRoles(), true);
        $canViewRequiredDocs = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::requiredDocumentCatalogRoles(), true);
        $canViewWorkflowQueue = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::workflowQueueRoles(), true);
        $canViewAttendance = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::institutionalMonitoringRoles(), true);
        $canViewEvaluations = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::institutionalMonitoringRoles(), true);
        $canViewAnnouncements = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::announcementViewerRoles(), true);
        $canViewMessages = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::messageParticipantRolesForStaffSender(), true);
        $canViewJournals = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::weeklyJournalViewerRoles(), true);
        $canViewDtr = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::dtrViewerRoles(), true);
        $canViewCertificates = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::certificateViewerRoles(), true);
        $canViewReports = $role && $role !== 'student' && in_array($role, \App\Support\InternshipRoles::reportsViewerRoles(), true);
        $showStudentsNav = $canViewStudents && \App\Support\InternshipRoles::staffSidebarShows($role, 'students');
        $showDeploymentsNav = $canViewDeployments && \App\Support\InternshipRoles::staffSidebarShows($role, 'deployments');
        $showCompaniesNav = $canViewCompanies && \App\Support\InternshipRoles::staffSidebarShows($role, 'companies');
        $showComplianceNav = $canViewCompliance && \App\Support\InternshipRoles::staffSidebarShows($role, 'compliance');
        $showRequiredDocsNav = $canViewRequiredDocs && \App\Support\InternshipRoles::staffSidebarShows($role, 'required-documents');
        $showWorkflowQueueNav = $canViewWorkflowQueue && \App\Support\InternshipRoles::staffSidebarShows($role, 'workflow-queue');
        $showAttendanceNav = $canViewAttendance && \App\Support\InternshipRoles::staffSidebarShows($role, 'attendance');
        $showEvalNav = $canViewEvaluations && \App\Support\InternshipRoles::staffSidebarShows($role, 'evaluations');
        $showAnnouncementNav = $canViewAnnouncements && \App\Support\InternshipRoles::staffSidebarShows($role, 'announcements');
        $showMessageNav = $canViewMessages && \App\Support\InternshipRoles::staffSidebarShows($role, 'messages');
        $showJournalNav = $canViewJournals && \App\Support\InternshipRoles::staffSidebarShows($role, 'weekly-journals');
        $showDtrNav = $canViewDtr && \App\Support\InternshipRoles::staffSidebarShows($role, 'dtr');
        $showCertificateNav = $canViewCertificates && \App\Support\InternshipRoles::staffSidebarShows($role, 'certificates');
        $showReportNav = $canViewReports && \App\Support\InternshipRoles::staffSidebarShows($role, 'reports');

        $pendingQueueCount = $role && $role !== 'student' && $showWorkflowQueueNav
            ? (function () use ($role) {
                $query = \App\Models\StudentDocument::where(function ($q) use ($role) {
                        $q->where('current_holder_role', $role)
                          ->orWhereNull('current_holder_role');
                    })
                    ->where(function ($q) {
                        $q->whereNotIn('workflow_status', ['completed', 'rejected'])
                          ->orWhereNull('workflow_status');
                    })
                    ->whereNotNull('file_path');

                $lastView = session('last_queue_view_at');
                if ($lastView) {
                    $query->where('last_action_at', '>', $lastView);
                }

                return $query->count();
            })()
            : 0;

        $pendingJournalCount = $role && $role === 'instructor' && $showJournalNav
            ? \App\Models\WeeklyJournal::where('status', 'submitted')
                ->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()))
                ->count()
            : 0;

        $onQueuePage = request()->routeIs('student-documents.queue');

        $showStaffRegistrySection = $showStudentsNav || $showCompaniesNav || $showDeploymentsNav;
        $showStaffDocumentsSection = $showRequiredDocsNav || $showComplianceNav || $showWorkflowQueueNav;
        $showStaffMonitoringSection = $showAttendanceNav || $showEvalNav || $showJournalNav || $showDtrNav || $showCertificateNav;
        $showStaffCommSection = $showAnnouncementNav || $showMessageNav;
        $unreadMessageCount = 0;
        $canViewCampusSettings = in_array($role, ['instructor'], true);
        $logoutRoute = $role === 'student' ? route('student.logout') : route('logout');
    @endphp

    @if ($role === 'student' || $isStudentSession)
        {{-- Mobile: Top bar with hamburger (student only) --}}
        <div class="md:hidden flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <a href="{{ $homeRoute }}" class="flex items-center gap-2">
                <x-application-logo class="h-8 w-auto" />
                <span class="text-sm font-semibold text-gray-800">{{ config('app.name') }}</span>
            </a>
            <div class="flex items-center gap-1">
                <button @click="open = !open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition-colors" aria-label="{{ __('Open menu') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path :class="{'hidden': open, 'block': !open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" class="block" />
                        <path :class="{'block': open, 'hidden': !open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" class="hidden" />
                    </svg>
                </button>
            </div>
        </div>
        {{-- Mobile: Fixed slide-over overlay --}}
        <div x-show="open" class="fixed inset-0 z-50 md:hidden" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>
            <div class="fixed inset-y-0 left-0 flex w-full max-w-xs flex-col bg-white shadow-xl"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                    <a href="{{ $homeRoute }}" class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-auto" />
                        <span class="text-sm font-semibold text-gray-800">{{ config('app.name') }}</span>
                    </a>
                    <button @click="open = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                    @if ($studentPortalLimited)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Student portal') }}</p>
                            <a href="{{ route('student.documents') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.documents*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('My Documents') }}</a>
                            <a href="{{ route('attendance.check-in') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('attendance.check-in') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Clock In / Out') }}</a>
                            <a href="{{ route('student.announcements') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.announcements*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Announcements') }}</a>
                        </div>
                        <p class="px-2 text-[11px] leading-snug text-amber-800">{{ __('More features unlock after deployment and mandatory documents are complete.') }}</p>
                    @else
                        <div class="space-y-1">
                            <a href="{{ route('student.dashboard') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.dashboard') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Dashboard') }}</a>
                        </div>
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Documents & attendance') }}</p>
                            <a href="{{ route('student.documents') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.documents*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('My Documents') }}</a>
                            <a href="{{ route('attendance.check-in') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('attendance.check-in*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Clock In / Out') }}</a>
                            <a href="{{ route('student.dtr.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.dtr*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Daily Time Record') }}</a>
                            <a href="{{ route('student.weekly-journals.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.weekly-journals*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Weekly Journal') }}</a>
                            <a href="{{ route('student.certificates.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.certificates*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Certificates') }}</a>
                        </div>
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Updates & messages') }}</p>
                            <a href="{{ route('student.announcements') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.announcements*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Announcements') }}</a>
                            <a href="{{ route('student.messages.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student.messages*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Messages') }}</a>
                        </div>
                    @endif
                </div>
                <div class="border-t border-gray-100 px-4 py-4 space-y-3">
                    <div class="font-medium text-sm text-gray-800">{{ $displayName }}</div>
                    <div class="text-xs text-gray-500">{{ $displayEmail }}</div>
                    <a href="{{ route('student.profile') }}" class="block text-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">{{ __('Profile') }}</a>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="w-full text-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-box-arrow-right me-1"></i>{{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- Staff: Mobile top nav with hamburger --}}
        <div class="md:hidden flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="h-8 w-auto" />
                <span class="text-sm font-semibold text-gray-800">{{ config('app.name') }}</span>
            </a>
            <button @click="open = !open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition-colors" aria-label="{{ __('Open menu') }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path :class="{'hidden': open, 'block': !open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" class="block" />
                    <path :class="{'block': open, 'hidden': !open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" class="hidden" />
                </svg>
            </button>
        </div>
        {{-- Staff mobile slide-over overlay --}}
        <div x-show="open" class="fixed inset-0 z-50 md:hidden" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-gray-900/60" @click="open = false"></div>
            <div class="fixed inset-y-0 left-0 flex w-full max-w-xs flex-col bg-white shadow-xl"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full">
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="h-8 w-auto" />
                        <span class="text-sm font-semibold text-gray-800">{{ config('app.name') }}</span>
                    </a>
                    <button @click="open = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-md transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                    @if ($showDashboardNav)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Overview') }}</p>
                            <a href="{{ route('dashboard') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('dashboard') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Dashboard') }}</a>
                        </div>
                    @endif
                    @if ($showStaffRegistrySection)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Registry & placement') }}</p>
                            @if ($showCompaniesNav)
                                <a href="{{ route('companies.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('companies.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Companies') }}</a>
                            @endif
                            @if ($showStudentsNav)
                                <a href="{{ route('students.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('students.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Students') }}</a>
                            @endif
                            @if ($showDeploymentsNav)
                                <a href="{{ route('deployments.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('deployments.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Deployments') }}</a>
                            @endif
                        </div>
                    @endif
                    @if ($showStaffDocumentsSection)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Documents & Requirements') }}</p>
                            @if ($showRequiredDocsNav)
                                <a href="{{ route('required-documents.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('required-documents.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Required Documents') }}</a>
                            @endif
                            @if ($showComplianceNav)
                                <a href="{{ route('compliance.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('compliance.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Compliance') }}</a>
                            @endif
                            @if ($showWorkflowQueueNav)
                                <a href="{{ route('student-documents.queue') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('student-documents.queue') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                                    {{ __('Document Queue') }}
                                    @if ($pendingQueueCount > 0 && !$onQueuePage)
                                        <span class="ml-1.5 inline-flex items-center justify-center w-2 h-2 rounded-full bg-red-500"></span>
                                    @endif
                                </a>
                            @endif
                        </div>
                    @endif
                    @if ($showStaffMonitoringSection)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Monitoring') }}</p>
                            @if ($showAttendanceNav)
                                <a href="{{ route('attendance.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('attendance.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Attendance') }}</a>
                            @endif
                            @if ($showEvalNav)
                                <a href="{{ route('evaluations.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('evaluations.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Evaluations') }}</a>
                            @endif
                            @if ($showJournalNav)
                                <a href="{{ route('weekly-journals.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('weekly-journals.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                                    {{ __('Weekly Journals') }}
                                    @if ($pendingJournalCount > 0 && !request()->routeIs('weekly-journals.*'))
                                        <span class="ml-1.5 inline-flex items-center justify-center w-2 h-2 rounded-full bg-red-500"></span>
                                    @endif
                                </a>
                            @endif
                            @if ($showDtrNav)
                                <a href="{{ route('dtr.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('dtr.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('DTR') }}</a>
                            @endif
                            @if ($showCertificateNav)
                                <a href="{{ route('certificates.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('certificates.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Certificates') }}</a>
                            @endif
                        </div>
                    @endif
                    @if ($showStaffCommSection)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Communication') }}</p>
                            @if ($showAnnouncementNav)
                                <a href="{{ route('announcements.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('announcements.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Announcements') }}</a>
                            @endif
                            @if ($showMessageNav)
                                <a href="{{ route('messages.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('messages.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Messages') }}</a>
                            @endif
                        </div>
                    @endif
                    @if ($showReportNav)
                        <div class="space-y-1">
                            <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Reports') }}</p>
                            <a href="{{ route('reports.index') }}" class="block px-2 py-2.5 text-sm {{ request()->routeIs('reports.*') ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">{{ __('Reports') }}</a>
                        </div>
                    @endif
                </div>
                <div class="border-t border-gray-100 px-4 py-4 space-y-3">
                    <div class="font-medium text-sm text-gray-800">{{ $displayName }}</div>
                    <div class="text-xs text-gray-500">{{ $displayEmail }}</div>
                    <div class="flex gap-2">
                        <a href="{{ route('profile.edit') }}" class="flex-1 text-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">{{ __('Profile') }}</a>
                        @if ($canViewCampusSettings)
                            <a href="{{ route('settings.campus.edit') }}" class="flex-1 text-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">{{ __('Settings') }}</a>
                        @endif
                    </div>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="w-full text-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-box-arrow-right me-1"></i>{{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Desktop sidebar (shared by all roles) --}}
    <div class="hidden md:flex md:flex-col h-full">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <a href="{{ $homeRoute }}" class="flex items-center gap-2">
                <x-application-logo class="h-9 w-auto" />
                <span class="text-sm font-semibold text-gray-800 leading-tight">{{ config('app.name') }}</span>
            </a>
        </div>

        <div class="flex-1 px-0 py-3 overflow-y-auto">
            @php $studentObjForNav = request()->attributes->get('student'); @endphp
            @if ($role === 'student')
                @if ($studentPortalLimited)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Student portal') }}</p>
                        <a href="{{ route('student.documents') }}" class="nav-link {{ request()->routeIs('student.documents*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text me-2"></i>{{ __('My Documents') }}</a>
                        @if ($studentObjForNav && $studentObjForNav->isDeploymentEligibleForPortal())
                            <a href="{{ route('attendance.check-in') }}" class="nav-link {{ request()->routeIs('attendance.check-in') ? 'active' : '' }}"><i class="bi bi-clock me-2"></i>{{ __('Clock In / Out') }}</a>
                        @endif
                        <a href="{{ route('student.announcements') }}" class="nav-link {{ request()->routeIs('student.announcements*') ? 'active' : '' }}"><i class="bi bi-megaphone me-2"></i>{{ __('Announcements') }}</a>
                    </div>
                @else
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Student portal') }}</p>
                        <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}"><i class="bi bi-house-door me-2"></i>{{ __('Dashboard') }}</a>
                        <a href="{{ route('student.documents') }}" class="nav-link {{ request()->routeIs('student.documents*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text me-2"></i>{{ __('My Documents') }}</a>
                        @if ($studentObjForNav && $studentObjForNav->isDeploymentEligibleForPortal())
                            <a href="{{ route('attendance.check-in') }}" class="nav-link {{ request()->routeIs('attendance.check-in*') ? 'active' : '' }}"><i class="bi bi-clock me-2"></i>{{ __('Clock In / Out') }}</a>
                        @endif
                        <a href="{{ route('student.dtr.index') }}" class="nav-link {{ request()->routeIs('student.dtr*') ? 'active' : '' }}"><i class="bi bi-calendar-week me-2"></i>{{ __('Daily Time Record') }}</a>
                        <a href="{{ route('student.weekly-journals.index') }}" class="nav-link {{ request()->routeIs('student.weekly-journals*') ? 'active' : '' }}"><i class="bi bi-journal-text me-2"></i>{{ __('Weekly Journal') }}</a>
                        <a href="{{ route('student.certificates.index') }}" class="nav-link {{ request()->routeIs('student.certificates*') ? 'active' : '' }}"><i class="bi bi-patch-check me-2"></i>{{ __('Certificates') }}</a>
                        <a href="{{ route('student.announcements') }}" class="nav-link {{ request()->routeIs('student.announcements*') ? 'active' : '' }}"><i class="bi bi-megaphone me-2"></i>{{ __('Announcements') }}</a>
                        <a href="{{ route('student.messages.index') }}" class="nav-link {{ request()->routeIs('student.messages*') ? 'active' : '' }}"><i class="bi bi-chat-dots me-2"></i>{{ __('Messages') }}</a>
                    </div>
                @endif
            @else
                @if ($showDashboardNav)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Overview') }}</p>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-house-door me-2"></i>{{ __('Dashboard') }}</a>
                    </div>
                @endif
                @if ($showStaffRegistrySection)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Registry & placement') }}</p>
                        @if ($showCompaniesNav)
                            <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}"><i class="bi bi-building me-2"></i>{{ __('Companies') }}</a>
                        @endif
                        @if ($showStudentsNav)
                            <a href="{{ route('students.index') }}" class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"><i class="bi bi-people me-2"></i>{{ __('Students') }}</a>
                        @endif
                        @if ($showDeploymentsNav)
                            <a href="{{ route('deployments.index') }}" class="nav-link {{ request()->routeIs('deployments.*') ? 'active' : '' }}"><i class="bi bi-briefcase me-2"></i>{{ __('Deployments') }}</a>
                        @endif
                    </div>
                @endif
                @if ($showStaffDocumentsSection)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Documents & Requirements') }}</p>
                        @if ($showRequiredDocsNav)
                            <a href="{{ route('required-documents.index') }}" class="nav-link {{ request()->routeIs('required-documents.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-check me-2"></i>{{ __('Required Documents') }}</a>
                        @endif
                        @if ($showComplianceNav)
                            <a href="{{ route('compliance.index') }}" class="nav-link {{ request()->routeIs('compliance.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check me-2"></i>{{ __('Compliance') }}</a>
                        @endif
                        @if ($showWorkflowQueueNav)
                            <a href="{{ route('student-documents.queue') }}" class="nav-link {{ request()->routeIs('student-documents.queue') ? 'active' : '' }}">
                                <i class="bi bi-diagram-3 me-2"></i>{{ __('Document Queue') }}
                                @if ($pendingQueueCount > 0 && !$onQueuePage)
                                    <span class="ml-auto inline-flex items-center justify-center w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                @endif
                            </a>
                        @endif
                    </div>
                @endif
                @if ($showStaffMonitoringSection)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Monitoring') }}</p>
                        @if ($showAttendanceNav)
                            <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}"><i class="bi bi-clock-history me-2"></i>{{ __('Attendance') }}</a>
                        @endif
                        @if ($showEvalNav)
                            <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.*') ? 'active' : '' }}"><i class="bi bi-star me-2"></i>{{ __('Evaluations') }}</a>
                        @endif
                        @if ($showJournalNav)
                            <a href="{{ route('weekly-journals.index') }}" class="nav-link {{ request()->routeIs('weekly-journals.*') ? 'active' : '' }}">
                                <i class="bi bi-journal-text me-2"></i>{{ __('Weekly Journals') }}
                                @if ($pendingJournalCount > 0 && !request()->routeIs('weekly-journals.*'))
                                    <span class="ml-auto inline-flex items-center justify-center w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                @endif
                            </a>
                        @endif
                        @if ($showDtrNav)
                            <a href="{{ route('dtr.index') }}" class="nav-link {{ request()->routeIs('dtr.*') ? 'active' : '' }}"><i class="bi bi-calendar-week me-2"></i>{{ __('DTR') }}</a>
                        @endif
                        @if ($showCertificateNav)
                            <a href="{{ route('certificates.index') }}" class="nav-link {{ request()->routeIs('certificates.*') ? 'active' : '' }}"><i class="bi bi-patch-check me-2"></i>{{ __('Certificates') }}</a>
                        @endif
                    </div>
                @endif
                @if ($showStaffCommSection)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Communication') }}</p>
                        @if ($showAnnouncementNav || $canViewAnnouncements)
                            <a href="{{ route('announcements.index') }}" class="nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}"><i class="bi bi-megaphone me-2"></i>{{ __('Announcements') }}</a>
                        @endif
                        @if ($showMessageNav)
                            <a href="{{ route('messages.index') }}" class="nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}"><i class="bi bi-chat-dots me-2"></i>{{ __('Messages') }}</a>
                        @endif
                    </div>
                @endif
                @if ($showReportNav)
                    <div class="space-y-1">
                        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ __('Reports') }}</p>
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph me-2"></i>{{ __('Reports') }}</a>
                    </div>
                @endif
            @endif
        </div>

        {{-- Desktop sidebar footer --}}
        <div class="p-4 border-t border-gray-100">
            <div class="font-medium text-sm text-gray-800">{{ $displayName }}</div>
            <div class="text-xs text-gray-500">{{ $displayEmail }}</div>
            <div class="mt-3 flex flex-col gap-2">
                <div class="flex gap-2">
                    @if ($staffUser)
                        <a href="{{ route('profile.edit') }}" class="flex-1 inline-flex items-center justify-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Profile') }}</a>
                    @elseif ($role === 'student')
                        <a href="{{ route('student.profile') }}" class="flex-1 inline-flex items-center justify-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Profile') }}</a>
                    @endif
                    @if ($canViewCampusSettings)
                        <a href="{{ route('settings.campus.edit') }}" class="flex-1 inline-flex items-center justify-center rounded-full border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">{{ __('Settings') }}</a>
                    @endif
                </div>
                <form method="POST" action="{{ $logoutRoute }}">
                    @csrf
                    <button type="submit" class="btn-signout w-full"><i class="bi bi-box-arrow-right me-2"></i>{{ __('Sign out') }}</button>
                </form>
            </div>
        </div>
    </div>
</nav>
