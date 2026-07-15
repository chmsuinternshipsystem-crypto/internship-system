<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div>
                <span class="block text-xs font-semibold text-gray-500">{{ __('Section') }}</span>
                <span class="text-sm text-gray-900 font-medium">{{ $student->section }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500">{{ __('Contact Number') }}</span>
                <span class="text-sm text-gray-900 font-medium">{{ \App\Support\PhoneHelper::formatPhone($student->contact_number) }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500">{{ __('Assigned Instructor') }}</span>
                <span class="text-sm text-gray-900 font-medium">{{ $student->assignedInstructor?->name ?? __('—') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500">{{ __('Student Account') }}</span>
                <span class="text-sm text-gray-900 font-medium">{{ $student->account?->email ?? __('No account') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-500">{{ __('OJT Type') }}</span>
                <span class="inline-flex items-center gap-1 mt-0.5 text-xs font-semibold px-2 py-0.5 rounded-full
                    {{ $student->ojt_type === 'internal' ? 'bg-indigo-100 text-indigo-800' : ($student->ojt_type === 'external' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700') }}">
                    @switch($student->ojt_type)
                        @case('internal') {{ __('Internal OJT') }} @break
                        @case('external') {{ __('External OJT') }} @break
                        @default {{ __('No Placement Yet') }}
                    @endswitch
                </span>
            </div>
            <div class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-500">{{ __('Document Progress') }}</span>
                <div class="mt-1 flex items-center gap-3">
                    <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-600 rounded-full" style="width: {{ $student->progress_pct_computed }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-emerald-700 tabular-nums">{{ $student->progress_pct_computed }}%</span>
                </div>
            </div>
            <div class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-500">{{ __('Journal Milestones') }}</span>
                <div class="mt-1 flex items-center gap-3">
                    <div class="flex-1 h-2.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $student->journal_progress_pct }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-blue-700 tabular-nums">{{ $student->journal_progress_pct }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Summary cards row --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    {{-- Deployment summary --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-5">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                <i class="bi bi-building text-gray-400"></i>
                {{ __('Deployment') }}
            </h3>
            @if ($latestDeployment)
                @php
                    $displayCompany = $latestDeployment->company?->name;
                    if (! $displayCompany) {
                        $displayCompany = $student->isInternalOjt() ? __('Internal OJT (School-based)') : __('No company assigned');
                    }
                    $companyLocked = $student->isInternalOjt() || ($latestDeployment->status === 'active' && is_null($latestDeployment->company_id));
                @endphp
                <p class="mt-2 text-sm text-gray-900 font-medium flex items-center gap-1.5">
                    {{ $displayCompany }}
                    @if ($companyLocked && is_null($latestDeployment->company_id))
                        <i class="bi bi-lock text-xs text-gray-400" title="{{ __('Internal OJT — company is school-based and cannot be changed.') }}"></i>
                    @endif
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $latestDeployment->start_date?->format('M d, Y') }}
                    @if ($latestDeployment->end_date)
                        &ndash; {{ $latestDeployment->end_date?->format('M d, Y') }}
                    @endif
                </p>
                @php $cls = match($latestDeployment->status) {
                    'active' => 'badge-active',
                    'completed' => 'badge-completed',
                    'withdrawn' => 'badge-withdrawn',
                    default => 'badge-default',
                }; @endphp
                <div class="mt-2">
                    <span class="status-badge {{ $cls }}">{{ Str::headline($latestDeployment->status) }}</span>
                </div>
                @if (! $latestDeployment->company_id && $canManage && ! $companyLocked)
                    <form method="POST" action="{{ route('deployments.assign-company', $latestDeployment) }}" class="mt-3 flex items-center gap-2"
                          x-data="{ company_id: '' }">
                        @csrf
                        <input type="hidden" name="return" value="student">
                        <select name="company_id" x-model="company_id" required
                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('Select company...') }}</option>
                            @foreach (\App\Models\Company::orderBy('name')->get() as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors shrink-0">
                            <i class="bi bi-plus-circle"></i>
                            {{ __('Assign') }}
                        </button>
                    </form>
                @elseif ($canManage && $latestDeployment->company_id)
                    <div class="mt-3">
                        <a href="{{ route('deployments.show', ['deployment' => $latestDeployment, 'return' => 'student']) }}"
                           class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                            {{ __('View details') }} &rarr;
                        </a>
                    </div>
                @endif
            @else
                <p class="mt-2 text-sm text-gray-500">{{ __('No deployment recorded.') }}</p>
                @if ($student->areAllPreDocsApproved())
                    <p class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                        <i class="bi bi-check-circle"></i>
                        {{ __('Auto-deployed upon document completion.') }}
                    </p>
                @else
                    <p class="mt-1 text-xs text-gray-400">{{ __('Deployment will be auto-created when all pre-requisite documents are approved.') }}</p>
                @endif
            @endif
        </div>
    </div>

    {{-- Compliance snapshot --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-5">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                <i class="bi bi-check-circle text-gray-400"></i>
                {{ __('Document Status') }}
            </h3>
            @php
                $cls = match($complianceVariant) {
                    'compliant' => 'badge-compliant',
                    'partial' => 'badge-partial',
                    'non_compliant' => 'badge-non-compliant',
                    default => 'badge-default',
                };
            @endphp
            <div class="mt-2">
                <span class="status-badge {{ $cls }}">{{ $complianceLabel }}</span>
            </div>
            @if ($totalMandatory > 0)
                <p class="text-xs text-gray-500 mt-1">
                    {{ __(':submitted of :total submitted', ['submitted' => $submittedMandatory, 'total' => $totalMandatory]) }}
                </p>
            @else
                <p class="text-xs text-gray-500 mt-1">{{ __('No mandatory documents configured.') }}</p>
            @endif
            <div class="mt-3 flex flex-col gap-1.5">
                <a href="{{ route('student-documents.edit', $student) }}"
                   class="inline-flex items-center text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                    {{ $canManage ? __('Update documents') : __('View documents') }} &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- OJT Grade --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-5">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                <i class="bi bi-star text-gray-400"></i>
                {{ __('OJT Grade') }}
                <span class="text-[10px] text-gray-400 font-normal">(70% HTE / 30% Instr.)</span>
            </h3>
            @if ($ojtGrade['is_complete'])
                <p class="mt-2 text-2xl font-semibold tabular-nums text-emerald-700">
                    {{ number_format((float) $ojtGrade['final_grade'], 2) }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('HTE') }}: {{ $ojtGrade['hte_score'] }}
                    &middot; {{ __('Instructor') }}: {{ $ojtGrade['instructor_score'] }}
                </p>
            @else
                <p class="mt-2 text-sm text-gray-500">
                    {{ __('Not yet computed.') }}
                </p>
                @if ($ojtGrade['hte_score'] === null)
                    <p class="text-xs text-gray-500 mt-1">{{ __('Missing: HTE evaluation.') }}</p>
                @endif
                @if ($ojtGrade['instructor_score'] === null)
                    <p class="text-xs text-gray-500 mt-1">{{ __('Missing: Instructor evaluation.') }}</p>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Remarks timeline --}}
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-chat-dots text-gray-400"></i>
                {{ __('Remarks & follow-ups') }}
            </h3>
        </div>

        @if ($canManage)
            <form action="{{ route('students.remarks.store', $student) }}" method="POST" class="space-y-2">
                @csrf
                <textarea name="content" rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
                    placeholder="{{ __('Add a new remark about this student...') }}">{{ old('content') }}</textarea>
                @error('content')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                        {{ __('Add Remark') }}
                    </button>
                </div>
            </form>
        @else
            <p class="text-sm text-gray-500 mb-4">
                {{ __('Read-only access: you can view remarks but cannot add new remarks.') }}
            </p>
        @endif

        @php $remarks = $student->remarks()->with('author')->latest()->paginate(5, ['*'], 'remarks_page'); @endphp
        <div class="space-y-4">
            @forelse ($remarks as $remark)
                <div class="flex items-start gap-3">
                    <div class="mt-1.5 h-2 w-2 rounded-full bg-emerald-500 shrink-0"></div>
                    <div class="flex-1 border border-gray-200 rounded-md px-3 py-2">
                        <p class="text-sm text-gray-900 whitespace-pre-line">{{ $remark->content }}</p>
                        <div class="mt-2 text-xs text-gray-500 flex justify-between items-center">
                            <span>{{ $remark->author?->name ?? __('Unknown author') }} &bull; {{ $remark->created_at?->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">{{ __('No remarks yet for this student.') }}</p>
            @endforelse
        </div>
        @if ($remarks->hasPages())
            <div class="mt-3">
                {{ $remarks->links() }}
            </div>
        @endif
    </div>
</div>

<div class="mt-6 flex justify-end gap-2">
    <a href="{{ route('students.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
        {{ __('Back to list') }}
    </a>
    @if ($student->account)
        <a href="{{ route('messages.create', ['student_account_id' => $student->account->id]) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
            <i class="bi bi-chat-dots"></i>
            {{ __('Send Message') }}
        </a>
    @endif
    @if ($canManage)
        <a href="{{ route('students.edit', $student) }}"
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
            {{ __('Edit profile') }}
        </a>
    @endif
</div>
