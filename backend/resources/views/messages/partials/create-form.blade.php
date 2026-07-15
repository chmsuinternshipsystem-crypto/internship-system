@php
    $isStudentPortal = $isStudentPortal ?? false;
    $isHtmxPartial = $isHtmxPartial ?? true;
    $storeRoute = $isStudentPortal ? 'student.messages.store' : 'messages.store';
    $indexRoute = $isStudentPortal ? 'student.messages.index' : 'messages.index';

    $recipientChipsData = $recipients->flatten(1)->values()->map(fn($r) => [
        'id' => (string) $r->id,
        'name' => $r->name,
        'role' => $r->role,
    ]);

    $studentChipsData = $studentAccountsForMessaging->flatten(1)->values()->map(fn($sa) => [
        'id' => (string) $sa->id,
        'student_number' => $sa->student_number,
        'name' => $sa->name,
        'section' => $sa->section,
        'deployed' => (bool) $sa->has_deployment,
        'pending_docs' => (bool) $sa->has_pending_documents,
    ]);

    $oldSelectedStaff = array_map('strval', old('participant_ids', []));
    $oldSelectedStudents = array_map('strval', old('student_account_ids', array_map('strval', $preSelectedStudentIds ?? [])));

    $messageFormJson = json_encode([
        'staffData' => $recipientChipsData,
        'studentData' => $studentChipsData,
        'selectedStaff' => $oldSelectedStaff,
        'selectedStudents' => $oldSelectedStudents,
    ]);
@endphp

<script>window.__mfd = {!! $messageFormJson !!};</script>

<form method="POST" action="{{ route($storeRoute) }}"
      @if ($isHtmxPartial) hx-post="{{ route($storeRoute) }}" hx-target="#message-conversation-panel" hx-swap="innerHTML" hx-disabled-elt="button[type=submit]" @endif
      @submit="clearDraft(); submitting = true"
      @htmx:after-request="submitting = false"
      x-data="messageDraft()"
      class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-full min-h-[500px]">
    @csrf

    {{-- Draft restored notice --}}
    <div x-show="draftRestored" x-cloak x-transition
         class="px-5 py-2 bg-amber-50 border-b border-amber-200 text-xs text-amber-700 shrink-0">
        <i class="bi bi-pencil me-1"></i>
        {{ __('Draft restored. Continue writing where you left off.') }}
    </div>

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 shrink-0">
        <div class="flex items-center gap-2">
            @if ($isHtmxPartial)
                <button type="button"
                        hx-get="{{ route($indexRoute) }}?empty=1"
                        hx-target="#message-conversation-panel"
                        hx-swap="innerHTML"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="bi bi-arrow-left text-base"></i>
                </button>
            @else
                <a href="{{ route($indexRoute) }}"
                   class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="bi bi-arrow-left text-base"></i>
                </a>
            @endif
            <h3 class="text-sm font-semibold text-gray-800">{{ __('New Message') }}</h3>
        </div>
        <div class="flex items-center gap-2">
            @if ($isHtmxPartial)
                <button type="button"
                        hx-get="{{ route($indexRoute) }}?empty=1"
                        hx-target="#message-conversation-panel"
                        hx-swap="innerHTML"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    {{ __('Cancel') }}
                </button>
            @else
                <a href="{{ route($indexRoute) }}"
                   class="rounded-lg border border-gray-200 bg-white px-4 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    {{ __('Cancel') }}
                </a>
            @endif
            <button type="submit"
                    :disabled="submitting"
                    class="rounded-lg bg-emerald-600 px-5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="bi bi-send me-1"></i>
                {{ __('Send') }}
            </button>
        </div>
    </div>

    {{-- Subject --}}
    <div class="px-5 pt-4 shrink-0">
        <x-text-input id="subject" name="subject" type="text" maxlength="255"
            class="block w-full" :value="old('subject')" required
            placeholder="{{ __('Subject') }}" />
        <x-input-error class="mt-1" :messages="$errors->get('subject')" />
    </div>

    {{-- Recipients --}}
    @if (! $isStudentPortal)
    <div class="px-5 pt-3 shrink-0"
         x-data="{
             staffData: window.__mfd.staffData,
             studentData: window.__mfd.studentData,
             selectedStaff: window.__mfd.selectedStaff,
             selectedStudents: window.__mfd.selectedStudents,
             modalOpen: false,
             activeTab: 'faculty',
             modalStaff: [], modalStudents: [],
             sectionFilter: '', studentSearch: '',

             get filteredStudents() { return this.studentData.filter(d => { if (this.sectionFilter && d.section !== this.sectionFilter) return false; if (this.studentSearch) { const q = this.studentSearch.toLowerCase(); if (!d.student_number.toLowerCase().includes(q) && !d.name.toLowerCase().includes(q)) return false; } return true; }); },
             get filteredStudentIds() { return this.filteredStudents.map(d => d.id); },
             get allFilteredSelected() { return this.filteredStudentIds.length > 0 && this.filteredStudentIds.every(id => this.modalStudents.includes(id)); },

             openModal() { this.modalStaff = [...this.selectedStaff]; this.modalStudents = [...this.selectedStudents]; this.activeTab = 'faculty'; this.modalOpen = true; },
             applySelections() { this.selectedStaff = [...this.modalStaff]; this.selectedStudents = [...this.modalStudents]; this.modalOpen = false; },
             closeModal() { this.modalOpen = false; },
             toggleSelectAll() {
                 if (this.allFilteredSelected) { this.modalStudents = this.modalStudents.filter(id => !this.filteredStudentIds.includes(id)); }
                 else { const existing = new Set(this.modalStudents); this.filteredStudentIds.forEach(id => existing.add(id)); this.modalStudents = [...existing]; }
             },
             selectByFilter(filter) { const ids = this.studentData.filter(d => { if (filter === 'deployed') return d.deployed; if (filter === 'undeployed') return !d.deployed; if (filter === 'pending_docs') return d.pending_docs; return true; }).map(d => d.id); const existing = new Set(this.modalStudents); ids.forEach(id => existing.add(id)); this.modalStudents = [...existing]; },
             get staffChips() { return this.staffData.filter(d => this.selectedStaff.includes(d.id)); },
             get studentChips() { return this.studentData.filter(d => this.selectedStudents.includes(d.id)); },
             removeStaff(id) { this.selectedStaff = this.selectedStaff.filter(s => s !== id); },
             removeStudent(id) { this.selectedStudents = this.selectedStudents.filter(s => s !== id); },
         }">
        <div class="flex items-center gap-2">
            <button type="button" @click="openModal()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-3 py-1.5 text-sm text-gray-600 hover:border-emerald-400 hover:text-emerald-700 hover:bg-emerald-50/50 transition-colors shrink-0">
                <i class="bi bi-plus-circle"></i>
                {{ __('Add Recipients') }}
            </button>
            <div class="flex flex-wrap gap-1 min-w-0">
                <template x-for="s in staffChips" :key="'s-'+s.id">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-2 py-0.5 text-xs">
                        <span class="font-medium text-gray-800" x-text="s.name"></span>
                        <span class="text-[10px] font-medium text-emerald-600" x-text="s.role.replace('_', ' ')"></span>
                        <button type="button" @click="removeStaff(s.id)" class="text-gray-400 hover:text-red-500 leading-none">&times;</button>
                    </span>
                </template>
                <template x-for="s in studentChips" :key="'st-'+s.id">
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 border border-blue-200 px-2 py-0.5 text-xs">
                        <span x-text="s.student_number + ' ' + s.name"></span>
                        <span class="text-[10px] font-medium text-blue-600" x-text="s.section"></span>
                        <button type="button" @click="removeStudent(s.id)" class="text-gray-400 hover:text-red-500 leading-none">&times;</button>
                    </span>
                </template>
            </div>
        </div>
        <template x-for="id in selectedStaff" :key="'h-s-'+id">
            <input type="hidden" name="participant_ids[]" :value="id">
        </template>
        <template x-for="id in selectedStudents" :key="'h-st-'+id">
            <input type="hidden" name="student_account_ids[]" :value="id">
        </template>
        <p class="text-xs text-gray-400 mt-1">{{ __('Selected recipients will see this thread.') }}</p>
        <x-input-error class="mt-1" :messages="$errors->get('participant_ids')" />
        <x-input-error class="mt-1" :messages="$errors->get('student_account_ids')" />

        {{-- Recipient Modal --}}
        <div x-show="modalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-start justify-center pt-12 sm:pt-20"
             @keydown.escape.window="closeModal()">
            <div class="fixed inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="relative z-10 w-full max-w-xl bg-white rounded-xl shadow-2xl max-h-[80vh] flex flex-col mx-4" @click.outside="closeModal()">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Select Recipients') }}</h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
                </div>
                <div class="flex border-b border-gray-200 px-5">
                    <button type="button" @click="activeTab = 'faculty'"
                            class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="activeTab === 'faculty' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'">{{ __('Faculty & Staff') }}</button>
                    @if ($studentAccountsForMessaging->isNotEmpty())
                    <button type="button" @click="activeTab = 'students'"
                            class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="activeTab === 'students' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'">{{ __('Students') }}</button>
                    @endif
                </div>
                <div x-show="activeTab === 'faculty'" class="flex-1 overflow-y-auto p-5 space-y-1" x-data="{ facultySearch: '' }">
                    <input type="text" x-model="facultySearch" placeholder="{{ __('Search by name...') }}" class="mb-3 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @forelse ($recipients as $role => $roleRecipients)
                        @php $roleDisplay = str_replace('_', ' ', ucwords((string) $role)); @endphp
                        <div class="px-4 py-1.5 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 rounded" x-show="facultySearch === ''">{{ $roleDisplay }}</div>
                        @foreach ($roleRecipients as $recipient)
                            <label x-show="facultySearch === '' || '{{ Str::lower($recipient->name) }}'.includes(facultySearch.toLowerCase())"
                                   class="flex items-center gap-3 px-4 py-2 text-sm cursor-pointer hover:bg-emerald-50/50 rounded-lg transition-colors">
                                <input type="checkbox" value="{{ $recipient->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" x-model="modalStaff">
                                <span class="font-medium text-gray-900">{{ $recipient->name }}</span>
                                <span class="ml-auto text-xs text-gray-500">{{ $roleDisplay }}</span>
                            </label>
                        @endforeach
                    @empty
                        <div class="p-4 text-sm text-gray-500 text-center">{{ __('No recipients available.') }}</div>
                    @endforelse
                </div>
                @if ($studentAccountsForMessaging->isNotEmpty())
                <div x-show="activeTab === 'students'" x-cloak class="flex-1 overflow-y-auto p-5 space-y-1">
                    <div class="flex gap-2 mb-2">
                        <select x-model="sectionFilter" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('All Sections') }}</option>
                            @foreach ($studentAccountsForMessaging->keys() as $sectionName)
                                <option value="{{ $sectionName }}">{{ $sectionName }}</option>
                            @endforeach
                        </select>
                        <input type="text" x-model="studentSearch" placeholder="{{ __('Search...') }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <button type="button" @click="selectByFilter('deployed')" class="text-xs rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700 hover:bg-emerald-100 transition-colors">{{ __('All Deployed') }}</button>
                        <button type="button" @click="selectByFilter('undeployed')" class="text-xs rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 font-medium text-amber-700 hover:bg-amber-100 transition-colors">{{ __('All Undeployed') }}</button>
                        <button type="button" @click="selectByFilter('pending_docs')" class="text-xs rounded-full border border-red-300 bg-red-50 px-2.5 py-1 font-medium text-red-700 hover:bg-red-100 transition-colors">{{ __('Pending Docs') }}</button>
                        <button type="button" @click="modalStudents = []" class="text-xs rounded-full border border-gray-300 bg-white px-2.5 py-1 font-medium text-gray-600 hover:bg-gray-100 transition-colors"><i class="bi bi-x-circle me-0.5"></i>{{ __('Clear') }}</button>
                    </div>
                    @forelse ($studentAccountsForMessaging as $sectionName => $sectionStudents)
                        <div x-show="sectionFilter === '' || sectionFilter === '{{ $sectionName }}'"
                             class="flex items-center justify-between px-4 py-1.5 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 rounded sticky top-0 z-10">
                            <span>{{ $sectionName }}</span>
                            <button type="button" @click="
                                const ids = {{ json_encode($sectionStudents->pluck('id')->map(fn($id) => (string) $id)->values()->all()) }};
                                const existing = new Set(modalStudents); const allSelected = ids.every(id => modalStudents.includes(id));
                                ids.forEach(id => allSelected ? existing.delete(id) : existing.add(id)); modalStudents = [...existing];
                            " class="text-[10px] font-medium text-emerald-600 hover:text-emerald-800 hover:underline">
                                <span x-text="(() => { const ids = {{ json_encode($sectionStudents->pluck('id')->map(fn($id) => (string) $id)->values()->all()) }}; return ids.every(id => modalStudents.includes(id)) ? '{{ __('Deselect All') }}' : '{{ __('Select All') }}'; })()"></span>
                            </button>
                        </div>
                        @foreach ($sectionStudents as $sa)
                            <label x-show="(sectionFilter === '' || sectionFilter === '{{ $sectionName }}') && (studentSearch === '' || '{{ Str::lower($sa->student_number) }}'.includes(studentSearch.toLowerCase()) || '{{ Str::lower($sa->name) }}'.includes(studentSearch.toLowerCase()))"
                                   class="flex items-center gap-3 px-4 py-2 text-sm cursor-pointer hover:bg-emerald-50/50 rounded-lg transition-colors">
                                <input type="checkbox" value="{{ $sa->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" x-model="modalStudents">
                                <span class="font-medium text-gray-900">{{ $sa->student_number }} — {{ $sa->name }}</span>
                                <span class="text-xs text-gray-500 ml-auto">{{ $sa->section }}</span>
                            </label>
                        @endforeach
                    @empty
                        <div class="p-4 text-sm text-gray-500 text-center">{{ __('No students available.') }}</div>
                    @endforelse
                </div>
                @endif
                <div class="flex items-center justify-between px-5 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                    <span class="text-sm text-gray-600"><span x-text="modalStaff.length + modalStudents.length"></span> {{ __('selected') }}</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeModal()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">{{ __('Cancel') }}</button>
                        <button type="button" @click="applySelections()" class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">{{ __('Done') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Student portal recipients --}}
    <div class="px-5 pt-3 shrink-0" x-data="{ recipientSearch: '' }">
        <input type="text" x-model="recipientSearch" placeholder="{{ __('Search faculty & staff...') }}" class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        <div class="mt-1 h-32 overflow-y-auto rounded-lg border border-gray-200 bg-white divide-y divide-gray-100">
            @forelse ($recipients as $role => $roleRecipients)
                @php $roleDisplay = str_replace('_', ' ', ucwords((string) $role)); @endphp
                @foreach ($roleRecipients as $recipient)
                    <label x-show="recipientSearch === '' || '{{ Str::lower($recipient->name) }}'.includes(recipientSearch.toLowerCase())"
                           class="flex items-center gap-3 px-4 py-2 text-sm cursor-pointer hover:bg-emerald-50/50 transition-colors">
                        <input type="checkbox" name="participant_ids[]" value="{{ $recipient->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               @checked(in_array((string) $recipient->id, old('participant_ids', []), true))>
                        <span class="font-medium text-gray-900">{{ $recipient->name }}</span>
                        <span class="ml-auto text-xs text-gray-500">{{ $roleDisplay }}</span>
                    </label>
                @endforeach
            @empty
                <div class="p-4 text-sm text-gray-500 text-center">{{ __('No recipients available.') }}</div>
            @endforelse
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ __('Select one or more recipients.') }}</p>
        <x-input-error class="mt-1" :messages="$errors->get('participant_ids')" />
    </div>
    @endif

    {{-- Message body (fills remaining space) --}}
    <div class="flex-1 flex flex-col px-5 pt-3 pb-4 min-h-0">
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-gray-500">{{ __('Message') }}</span>
            <span class="text-xs text-gray-400" x-text="body.length + '/3000'"></span>
        </div>
        <textarea id="body" name="body" rows="" maxlength="3000"
            x-model="body"
            x-on:input="saveDraft()"
            class="flex-1 w-full rounded-lg border-2 border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 resize-none focus:border-emerald-400 focus:ring-emerald-400/20 transition-colors min-h-[120px]"
            required
            placeholder="{{ __('Write your message here...') }}"></textarea>
        <x-input-error class="mt-1" :messages="$errors->get('body')" />
    </div>
</form>

<script>
(function () {
    var STORAGE_KEY = 'msg_draft_' + ('{{ auth()->id() ?? 'student' }}');
    if (typeof window.messageDraft === 'undefined') {
        window.messageDraft = function () {
            var saved = '';
            try { saved = sessionStorage.getItem(STORAGE_KEY) || ''; } catch (e) {}
            return {
                body: saved,
                submitting: false,
                draftRestored: saved !== '',
                saveDraft: function () {
                    try { sessionStorage.setItem(STORAGE_KEY, this.body); } catch (e) {}
                },
                clearDraft: function () {
                    try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
                },
            };
        };
    }
    // Clear draft on successful HTMX submit
    document.addEventListener('htmx:afterRequest', function (e) {
        if (e.detail.successful && e.detail.target && e.detail.target.closest && e.detail.target.closest('#message-conversation-panel')) {
            try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
        }
    });
})();
</script>
