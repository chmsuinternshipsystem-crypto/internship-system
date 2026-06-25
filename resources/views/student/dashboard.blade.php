<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Student Portal') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Dashboard') }}</h2>
        </div>
    </x-slot>

    {{-- Completion banner --}}
    @if (! empty($showCompletion) && $showCompletion)
        <x-page-card compact class="mb-6 border-emerald-200 bg-emerald-50/50">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check2-all text-2xl"></i>
                </span>
                <div>
                    <h3 class="text-lg font-bold text-emerald-900">{{ __('All Requirements Complete!') }}</h3>
                    <p class="text-sm text-emerald-700 mt-0.5">{{ __('You have submitted all required documents and your deployment is completed. Great job!') }}</p>
                </div>
            </div>
        </x-page-card>
    @endif

    {{-- Welcome Card with Attendance Passcode --}}
    <div class="rounded-xl bg-emerald-600 p-5 shadow-sm mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-emerald-100">{{ __('Welcome back') }},</p>
                <h3 class="text-xl font-bold text-white truncate">{{ $student->name }}</h3>
                <p class="mt-0.5 text-sm text-emerald-100">
                    {{ $student->program }} · {{ __('Year') }} {{ $student->year_level }} · {{ __('Section') }} {{ $student->section }}
                </p>
                <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-emerald-500/40 px-3 py-1 text-xs font-semibold text-emerald-50">
                    <i class="bi bi-person-badge"></i>
                    {{ $student->student_number }}
                </span>
            </div>

            @if (! empty($attendancePasscode))
                <div class="flex-shrink-0 w-full sm:w-auto"
                     x-data="{ showPasscode: false, copied: false }">
                    <div class="rounded-lg bg-white/10 backdrop-blur-sm px-4 py-3 sm:px-5 min-w-[200px]">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-200">{{ __('Attendance Passcode') }}</p>
                        <p class="mt-1 text-2xl font-mono font-bold text-white text-center tracking-[0.3em]"
                            x-text="showPasscode ? '{{ $attendancePasscode }}' : '••••••'"></p>
                        <div class="mt-2 flex gap-1.5 justify-center">
                            <button type="button"
                                    @click="showPasscode = !showPasscode"
                                    class="inline-flex items-center justify-center gap-1 rounded-md bg-white/20 px-2.5 py-1 text-xs font-semibold text-white hover:bg-white/30 transition-colors min-w-[80px] whitespace-nowrap">
                                <i class="bi" :class="showPasscode ? 'bi-eye-slash' : 'bi-eye'"></i>
                                <span x-text="showPasscode ? '{{ __('Hide') }}' : '{{ __('Reveal') }}'"></span>
                            </button>
                            <button type="button"
                                    @click="navigator.clipboard && navigator.clipboard.writeText('{{ $attendancePasscode }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    class="inline-flex items-center justify-center gap-1 rounded-md bg-white/20 px-2.5 py-1 text-xs font-semibold text-white hover:bg-white/30 transition-colors min-w-[80px] whitespace-nowrap">
                                <i class="bi" :class="copied ? 'bi-check-lg' : 'bi-clipboard'"></i>
                                <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"></span>
                            </button>
                        </div>
                        @if (! empty($attendancePasscodeGeneratedAt))
                            <p class="mt-1.5 text-[10px] text-emerald-200">
                                <i class="bi bi-clock me-0.5"></i>{{ __('Generated') }} {{ $attendancePasscodeGeneratedAt->format('M d, Y h:i A') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Getting Started (new students only) --}}
    @if (! empty($showGettingStarted) && $showGettingStarted)
        <x-page-card compact class="mb-6 border-emerald-200 bg-emerald-50/30">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="bi bi-rocket-takeoff text-lg"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Welcome to the Internship Portal!') }}</h3>
                    <p class="text-xs text-gray-600 mt-1">{{ __('Here\'s what to do next:') }}</p>
                    <ol class="mt-3 space-y-2 text-sm">
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">1</span>
                            <span>{{ __('Upload your required documents in the Documents section') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">2</span>
                            <span>{{ __('Start clocking in using the attendance feature') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">3</span>
                            <span>{{ __('Submit your weekly journals regularly') }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </x-page-card>
    @endif

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-page-card compact>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Documents') }}</p>
                    <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $submittedCount }}/{{ $totalMandatory }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ __('Mandatory documents') }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="bi bi-file-earmark-text text-lg"></i>
                </span>
            </div>
            @if ($totalMandatory > 0)
                <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ ($submittedCount / $totalMandatory) * 100 }}%"></div>
                </div>
            @endif
        </x-page-card>

        <x-page-card compact>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Deployment') }}</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1 leading-tight">
                        @if ($latestDeployment?->company)
                            {{ $latestDeployment->company->name }}
                        @elseif ($student->isInternalOjt())
                            {{ __('Internal OJT (School-based)') }}
                        @else
                            {{ __('Not assigned') }}
                        @endif
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="inline-flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full inline-block
                                @switch($latestDeployment?->status ?? 'pending')
                                    @case('active') bg-emerald-500 @break
                                    @case('completed') bg-blue-500 @break
                                    @default bg-amber-500 @endswitch
                            "></span>
                            {{ ucfirst((string) ($latestDeployment?->status ?? 'pending')) }}
                        </span>
                    </p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i class="bi bi-briefcase text-lg"></i>
                </span>
            </div>
        </x-page-card>

        <x-page-card compact>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Attendance') }}</p>
                    @if ($todayAttendance)
                        @if ($todayAttendance->time_out_at)
                            <p class="text-sm font-semibold text-gray-900 mt-1 leading-tight">{{ __('Completed') }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $todayAttendance->check_in_at->format('h:i A') }} – {{ $todayAttendance->time_out_at->format('h:i A') }}
                            </p>
                            @php $mins = (int) $todayAttendance->total_minutes; $h = intdiv($mins, 60); $m = $mins % 60; @endphp
                            @if ($h > 0 || $m > 0)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $h }}h {{ $m }}m {{ __('rendered') }}</p>
                            @endif
                        @else
                            <p class="text-sm font-semibold text-emerald-700 mt-1 leading-tight">{{ __('Checked in') }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="bi bi-check-circle-fill text-emerald-500 me-0.5"></i>{{ $todayAttendance->check_in_at->format('h:i A') }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('Remember to time out at the end of your duty.') }}</p>
                        @endif
                    @else
                        <p class="text-lg font-semibold text-gray-900 mt-1 leading-tight">{{ __('—') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('No check-in today') }}</p>
                    @endif
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <i class="bi bi-clock-history text-lg"></i>
                </span>
            </div>
        </x-page-card>

        <x-page-card compact>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('OJT Grade') }}</p>
                    @if ($ojtGrade['is_complete'])
                        <p class="text-2xl font-semibold tabular-nums text-emerald-700 mt-1">
                            {{ number_format((float) $ojtGrade['final_grade'], 2) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ __('HTE') }} {{ $ojtGrade['hte_score'] }} · {{ __('Inst.') }} {{ $ojtGrade['instructor_score'] }}
                        </p>
                    @else
                        <p class="text-sm text-gray-600 mt-1 leading-tight">{{ __('Pending evaluation') }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ __('Awaiting HTE & instructor scores') }}</p>
                    @endif
                </div>
                <div class="flex flex-col items-end gap-2">
                    @if ($hteEvaluation)
                        <a href="{{ route('student.evaluations.export', $hteEvaluation) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 border border-transparent rounded-lg text-xs font-semibold text-white hover:bg-emerald-700 transition-colors shadow-sm">
                            <i class="bi bi-download"></i>
                            {{ __('Download Eval. Form') }}
                        </a>
                    @endif
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                        <i class="bi bi-trophy text-lg"></i>
                    </span>
                </div>
            </div>
        </x-page-card>
    </div>

    {{-- Alerts + Announcements --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-page-card compact>
            <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="bi bi-exclamation-triangle text-amber-500"></i>
                {{ __('Alerts & Reminders') }}
            </h3>
            @if ($alerts->isNotEmpty())
                <ul class="space-y-2 text-sm text-gray-700">
                    @foreach ($alerts as $alert)
                        <li class="rounded-md bg-amber-50 border border-amber-200 px-3 py-2.5 flex items-start gap-2">
                            <i class="bi bi-dot text-amber-500 mt-0.5"></i>
                            <span>{{ $alert }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">{{ __('No alerts at this time.') }}</p>
            @endif
            @if ($missingCount > 0)
                <a href="{{ route('student.documents') }}" class="inline-flex items-center mt-3 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                    <i class="bi bi-arrow-right me-1"></i>
                    {{ __('View document checklist') }}
                </a>
            @endif
        </x-page-card>

        <x-page-card compact>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-megaphone text-emerald-500"></i>
                    {{ __('Latest Announcements') }}
                </h3>
                <a href="{{ route('student.announcements') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    {{ __('View all') }}
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <div class="space-y-2">
                @forelse ($announcements as $announcement)
                    <div class="rounded-lg border border-gray-200 px-3 py-2.5 hover:border-emerald-200 transition-colors">
                        <p class="font-medium text-gray-900 text-sm">{{ $announcement->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-2">
                            <i class="bi bi-clock"></i>
                            {{ $announcement->created_at?->format('M d, Y h:i A') }}
                            @if ($announcement->author)
                                <span class="text-gray-400">·</span>
                                <span>{{ $announcement->author->name }}</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-700 mt-1.5">{{ \Illuminate\Support\Str::limit($announcement->body, 150) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-6 text-center">{{ __('No announcements yet.') }}</p>
                @endforelse
            </div>
        </x-page-card>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="page-card-inner page-card-inner--compact">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">{{ __('Quick Actions') }}</p>
@php
    $todayAtt = null;
    if ($todayAttendance) {
        $todayAtt = \App\Models\Attendance::find($todayAttendance->id);
    }

    if (! $todayAtt) {
        $attendanceAction = 'am_time_in';
        $attendanceLabel = __('AM Clock In');
        $btnColor = 'border-emerald-200 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800';
        $iconColor = 'text-emerald-600';
    } elseif ($todayAtt->am_check_in && ! $todayAtt->am_check_out) {
        $attendanceAction = 'am_time_out';
        $attendanceLabel = __('AM Time Out');
        $btnColor = 'border-emerald-200 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800';
        $iconColor = 'text-emerald-600';
    } elseif ($todayAtt->am_check_out && ! $todayAtt->pm_check_in) {
        $attendanceAction = 'pm_time_in';
        $attendanceLabel = __('PM Clock In');
        $btnColor = 'border-indigo-200 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800';
        $iconColor = 'text-indigo-600';
    } elseif ($todayAtt->pm_check_in && ! $todayAtt->pm_check_out) {
        $attendanceAction = 'pm_time_out';
        $attendanceLabel = __('PM Time Out');
        $btnColor = 'border-indigo-200 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-800';
        $iconColor = 'text-indigo-600';
    } else {
        $attendanceAction = 'completed';
        $attendanceLabel = __('Done Today');
        $btnColor = 'border-gray-200 hover:border-gray-300';
        $iconColor = 'text-gray-400';
    }
    $attendanceIcon = match ($attendanceAction) { 'am_time_out' => 'bi-box-arrow-left text-sky-600', 'pm_time_out' => 'bi-box-arrow-left text-sky-600', 'completed' => 'bi-check-circle text-gray-400', default => 'bi-clock text-emerald-600' };
@endphp
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2"
                 x-data="quickClock()">
                <button type="button"
                        @click="clock()"
                        x-show="action !== 'completed'"
                        :disabled="loading"
                        class="flex flex-col items-center gap-1.5 rounded-lg border bg-white px-2 py-3 text-xs font-semibold text-gray-700 transition-all duration-150 disabled:opacity-50 disabled:cursor-wait {{ $btnColor }}">
                    <i class="bi text-xl {{ $iconColor }}" :class="loading ? 'bi-arrow-repeat animate-spin text-amber-500' : '{{ $iconColor === 'text-emerald-600' ? 'bi-clock' : ($iconColor === 'text-indigo-600' ? 'bi-clock' : 'bi-check-circle') }}'"></i>
                    <span x-text="loading ? '{{ __('Please wait...') }}' : '{{ $attendanceLabel }}'"></span>
                </button>
                <a href="{{ route('attendance.check-in') }}{{ $attendanceAction === 'time_out' ? '?action=time_out' : '' }}"
                   x-show="action === 'completed'"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-gray-300 transition-all duration-150">
                    <i class="bi bi-check-circle text-gray-400 text-xl"></i>
                    <span>{{ __('Done Today') }}</span>
                </a>
                <a href="{{ route('student.documents') }}"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition-all duration-150">
                    <i class="bi bi-file-earmark-text text-xl text-emerald-600"></i>
                    <span>{{ __('Documents') }}</span>
                </a>
                <a href="{{ route('student.dtr.index') }}"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition-all duration-150">
                    <i class="bi bi-clock-history text-xl text-emerald-600"></i>
                    <span>{{ __('DTR') }}</span>
                </a>
                <a href="{{ route('student.weekly-journals.index') }}"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition-all duration-150">
                    <i class="bi bi-journal-text text-xl text-emerald-600"></i>
                    <span>{{ __('Journal') }}</span>
                </a>
                <a href="{{ route('student.certificates.index') }}"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition-all duration-150">
                    <i class="bi bi-patch-check text-xl text-emerald-600"></i>
                    <span>{{ __('Certificates') }}</span>
                </a>
                <a href="{{ route('student.messages.index') }}"
                   class="flex flex-col items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-2 py-3 text-xs font-semibold text-gray-700 hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 transition-all duration-150">
                    <i class="bi bi-chat-dots text-xl text-emerald-600"></i>
                    <span>{{ __('Messages') }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Clock-In toast --}}
    <div x-show="qcToastShow"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-4 right-4 z-50 max-w-sm"
         style="display: none;">
        <div class="rounded-lg px-4 py-3 text-white shadow-lg"
             :class="qcToastSuccess ? 'bg-emerald-600' : 'bg-rose-600'">
            <div class="flex items-center gap-3">
                <i :class="qcToastSuccess ? 'bi-check-circle-fill' : 'bi-x-circle-fill'" class="text-xl"></i>
                <p class="text-sm font-semibold" x-text="qcToastMessage"></p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function quickClock() {
            return {
                loading: false,
                action: '{{ $attendanceAction }}',
                qcToastShow: false,
                qcToastSuccess: false,
                qcToastMessage: '',
                clock() {
                    if (this.loading) return;

                    if (!navigator.geolocation) {
                        window.location.href = '{{ route('attendance.check-in') }}';
                        return;
                    }

                    this.loading = true;

                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const data = new URLSearchParams();
                            data.append('latitude', pos.coords.latitude);
                            data.append('longitude', pos.coords.longitude);
                            data.append('accuracy_meters', pos.coords.accuracy || '');

                            fetch('{{ route('student.attendance.quick-clock') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: data.toString(),
                            })
                            .then(r => r.json())
                            .then(data => {
                                this.loading = false;
                                if (data.success) {
                                    this.qcToastSuccess = true;
                                    this.qcToastMessage = data.action_label + ' @ ' + data.time;
                                    this.qcToastShow = true;
                                    setTimeout(() => { window.location.reload(); }, 2000);
                                } else {
                                    this.qcToastSuccess = false;
                                    this.qcToastMessage = data.error || '{{ __('Clock failed. Redirecting...') }}';
                                    this.qcToastShow = true;
                                    setTimeout(() => { window.location.reload(); }, 1500);
                                }
                            })
                            .catch(() => {
                                this.loading = false;
                                this.qcToastSuccess = false;
                                this.qcToastMessage = '{{ __('Network error. Redirecting...') }}';
                                this.qcToastShow = true;
                                setTimeout(() => { window.location.reload(); }, 1500);
                            });
                        },
                        () => {
                            this.loading = false;
                            window.location.href = '{{ route('attendance.check-in') }}';
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                    );
                },
            }
        }
    </script>
    @endpush
</x-app-layout>
