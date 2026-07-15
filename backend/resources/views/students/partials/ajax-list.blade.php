@if ($canManage)
<div class="overflow-x-auto overflow-y-visible" x-init="items = []; restorePersisted()" data-total-matching="{{ $totalMatching ?? 0 }}">

        {{-- Select all matching prompt --}}
        <div x-cloak x-show="showSelectAllPrompt"
             class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 mb-3 flex items-center justify-between text-sm">
            <span class="text-amber-900">
                {{ __('All') }} <span x-text="items.length"></span> {{ __('on this page selected.') }}
                <button @click="selectAllMatchingFn()" class="font-semibold text-amber-800 underline hover:text-amber-900 ml-1">
                    {{ __('Select all') }} <span x-text="totalMatching"></span> {{ __('students matching this filter.') }}
                </button>
            </span>
            <button @click="showSelectAllPrompt = false" class="text-amber-600 hover:text-amber-800 ml-2">&times;</button>
        </div>

        <table class="min-w-full divide-y divide-gray-200 custom-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2 w-10">
                        <input
                            type="checkbox"
                            @change="toggleAll($event.target.checked)"
                            :checked="selectAllMatching || (selected.length === items.length && items.length > 0)"
                            class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                        >
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student Number') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Section') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Instructor') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Progress') }}</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($students as $student)
                    <tr x-init="register({{ $student->id }})" :class="{ 'bg-emerald-50/50': isSelected({{ $student->id }}) }">
                        <td class="px-2 py-2">
                            <input
                                type="checkbox"
                                :checked="isSelected({{ $student->id }})"
                                @change="toggle({{ $student->id }})"
                                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                            >
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->student_number }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->section }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $student->assignedInstructor?->name ?? __('—') }}</td>
                         <td class="px-4 py-2 text-sm">
                            @php
                                $pd = $progressData[$student->id] ?? [];
                                $docPct = $pd['doc_pct'] ?? 0;
                                $journalPct = $pd['journal_pct'] ?? $student->journal_progress_pct;
                                $dtrPct = $pd['dtr_pct'] ?? $student->dtr_progress_pct;
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-600" style="width: {{ $docPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-emerald-700 tabular-nums">{{ $docPct }}%</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600" style="width: {{ $dtrPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-blue-700 tabular-nums">{{ $dtrPct }}%</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-violet-500" style="width: {{ $journalPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-violet-700 tabular-nums">{{ $journalPct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                            <x-action-menu :id="'student-'.$student->id">
                                <a href="{{ route('students.show', $student) }}">
                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                </a>
                                @if ($canManage)
                                    <a href="{{ route('students.edit', $student) }}">
                                        <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                    </a>
                                    <div class="action-divider"></div>
                                    <button type="button" @click="$dispatch('open-archive-modal', { id: {{ $student->id }}, name: @js($student->name) })"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                                        <i class="bi bi-archive"></i> {{ __('Archive') }}
                                    </button>
                                @endif
                            </x-action-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div class="flex flex-col items-center justify-center py-8">
                                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50 border border-gray-100 mb-4">
                                    <i class="bi bi-people text-2xl text-gray-400"></i>
                                </span>
                                <strong class="text-sm font-semibold text-gray-700">{{ __('No students yet') }}</strong>
                                <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ __('Create your first student profile to get started with internship management.') }}</p>
                                @if ($canManage)
                                    <a href="{{ route('students.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm btn-lift">
                                        <i class="bi bi-plus-lg"></i>
                                        {{ __('Create Student Profile') }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    {{-- Non-manage view (read-only, no checkboxes) --}}
    <div class="overflow-x-auto overflow-y-visible">
        <table class="min-w-full divide-y divide-gray-200 custom-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student Number') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Section') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Instructor') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Progress') }}</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($students as $student)
                    @php
                        $pd = $progressData[$student->id] ?? [];
                        $readDocPct = $pd['doc_pct'] ?? 0;
                        $readJournalPct = $pd['journal_pct'] ?? $student->journal_progress_pct;
                        $readDtrPct = $pd['dtr_pct'] ?? $student->dtr_progress_pct;
                    @endphp
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->student_number }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student->section }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $student->assignedInstructor?->name ?? __('—') }}</td>
                        <td class="px-4 py-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-600" style="width: {{ $readDocPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-emerald-700 tabular-nums">{{ $readDocPct }}%</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-600" style="width: {{ $readDtrPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-blue-700 tabular-nums">{{ $readDtrPct }}%</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-violet-500" style="width: {{ $readJournalPct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-violet-700 tabular-nums">{{ $readJournalPct }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                            <x-action-menu :id="'student-'.$student->id">
                                <a href="{{ route('students.show', $student) }}">
                                    <i class="bi bi-eye"></i> {{ __('View') }}
                                </a>
                            </x-action-menu>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            <div class="flex flex-col items-center justify-center py-8">
                                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50 border border-gray-100 mb-4">
                                    <i class="bi bi-people text-2xl text-gray-400"></i>
                                </span>
                                <strong class="text-sm font-semibold text-gray-700">{{ __('No students found') }}</strong>
                                <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ __('Try adjusting your search or filter criteria.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@include('partials.htmx-pagination', ['paged' => $students, 'hxTarget' => '#students-table-mount', 'hxPushUrl' => true])

@once
<script>
function batchSelect(totalMatching, batchCleared) {
    if (batchCleared) {
        try {
            sessionStorage.removeItem('batch_std_{{ auth()->id() }}_%_%_%');
            // Couldn't match exact key, so clear any key with this prefix
            var prefix = 'batch_std_{{ auth()->id() }}_';
            for (var i = 0; i < sessionStorage.length; i++) {
                var key = sessionStorage.key(i);
                if (key && key.indexOf(prefix) === 0) {
                    sessionStorage.removeItem(key);
                    i--;
                }
            }
        } catch(e) {}
    }
    return {
        selected: [],
        deselected: [],
        items: [],
        totalMatching: totalMatching || 0,
        selectAllMatching: false,
        showSelectAllPrompt: false,
        storageKey: 'batch_std_{{ auth()->id() }}_{{ $section ?? '' }}_{{ ($myStudents ?? false) ? 'my' : 'all' }}_{{ $deploymentStatus ?? '' }}_{{ ($noCompany ?? false) ? 'nc' : '' }}_{{ md5($search ?? '') }}',
        register(id) {
            if (!this.items.includes(id)) {
                this.items.push(id);
            }
            this.updateSelectAllPrompt();
        },
        toggle(id) {
            if (this.selectAllMatching) {
                const idx = this.deselected.indexOf(id);
                if (idx === -1) {
                    this.deselected.push(id);
                } else {
                    this.deselected.splice(idx, 1);
                }
                this.persist();
                return;
            }
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
                this.updateSelectAllPrompt();
            } else {
                this.selected.splice(idx, 1);
                this.showSelectAllPrompt = false;
            }
            this.persist();
        },
        toggleAll(checked) {
            if (this.selectAllMatching && !checked) {
                this.selectAllMatching = false;
                this.showSelectAllPrompt = false;
                this.selected = [];
                this.deselected = [];
                this.persistClear();
                return;
            }
            this.selected = checked ? [...this.items] : [];
            this.showSelectAllPrompt = false;
            if (checked && this.totalMatching > this.items.length) {
                this.showSelectAllPrompt = true;
            }
            this.persist();
        },
        selectAllMatchingFn() {
            this.selectAllMatching = true;
            this.selected = [];
            this.deselected = [];
            this.showSelectAllPrompt = false;
            this.persist();
        },
        clear() {
            this.selected = [];
            this.deselected = [];
            this.selectAllMatching = false;
            this.showSelectAllPrompt = false;
            this.persistClear();
        },
        isSelected(id) {
            if (this.selectAllMatching) {
                return !this.deselected.includes(id);
            }
            return this.selected.includes(id);
        },
        updateSelectAllPrompt() {
            if (this.selectAllMatching) {
                this.showSelectAllPrompt = false;
                return;
            }
            if (this.selected.length === this.items.length && this.items.length > 0 && this.totalMatching > this.items.length) {
                this.showSelectAllPrompt = true;
            } else {
                this.showSelectAllPrompt = false;
            }
        },
        persist() {
            try {
                sessionStorage.setItem(this.storageKey, JSON.stringify(this.selected));
                sessionStorage.setItem(this.storageKey + '_all', this.selectAllMatching ? '1' : '');
                sessionStorage.setItem(this.storageKey + '_dex', JSON.stringify(this.deselected));
            } catch(e) {}
        },
        persistClear() {
            try {
                sessionStorage.removeItem(this.storageKey);
                sessionStorage.removeItem(this.storageKey + '_all');
                sessionStorage.removeItem(this.storageKey + '_dex');
            } catch(e) {}
        },
        restorePersisted() {
            try {
                var stored = sessionStorage.getItem(this.storageKey);
                if (stored) {
                    this.selected = JSON.parse(stored);
                    this.updateSelectAllPrompt();
                }
                var allFlag = sessionStorage.getItem(this.storageKey + '_all');
                if (allFlag === '1') {
                    this.selectAllMatching = true;
                    this.selected = [];
                }
                var dex = sessionStorage.getItem(this.storageKey + '_dex');
                if (dex) {
                    this.deselected = JSON.parse(dex);
                }
                this.updateSelectAllPrompt();
            } catch(e) {}
        },
    };
}
</script>
@endonce
