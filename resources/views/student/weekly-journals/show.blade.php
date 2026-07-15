<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Weekly Journal') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Week') }} #{{ $weeklyJournal->week_number }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $weeklyJournal->week_start_date->format('M d, Y') }} – {{ $weeklyJournal->week_end_date->format('M d, Y') }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $statusCls = match($weeklyJournal->status) {
                        'reviewed' => 'badge-completed',
                        'submitted' => 'badge-active',
                        default => 'badge-default',
                    };
                @endphp
                <span class="status-badge {{ $statusCls }} text-sm">{{ Str::headline($weeklyJournal->status) }}</span>
            </div>
        </div>
    </x-slot>

    @php
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $start = $weeklyJournal->week_start_date->copy();
        $existingActivities = $weeklyJournal->activities ?? [];
        $existingFiles = collect($weeklyJournal->files ?? [])->map(fn ($f) => array_merge($f, [
            'url' => route('student.weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $f['day']]),
        ]))->all();
    @endphp

    @if ($weeklyJournal->is_late)
        <div class="max-w-7xl mx-auto">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center gap-2">
                <i class="bi bi-clock-history"></i>
                <span>{{ __('Submitted after the week ended — marked as late.') }}</span>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto space-y-6"
         x-data="weeklyJournalEditor({
             journalId: {{ $weeklyJournal->id }},
             activities: @js($existingActivities),
             files: @js($existingFiles),
             canEdit: {{ $weeklyJournal->isEditable() ? 'true' : 'false' }},
             csrf: '{{ csrf_token() }}',
             baseUrl: '/student/weekly-journals/{{ $weeklyJournal->id }}'
         })">

        {{-- Saving indicator --}}
        <div x-show="saving || lastSaved" x-cloak
             class="fixed top-4 right-4 z-50 flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium shadow-lg transition-all"
             :class="saving ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
            <span x-show="saving">
                <i class="bi bi-arrow-repeat animate-spin me-1"></i>{{ __('Saving...') }}
            </span>
            <span x-show="!saving && lastSaved">
                <i class="bi bi-check-circle me-1"></i>{{ __('Saved') }}
            </span>
        </div>

        {{-- Activities Table --}}
        <x-page-card compact>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-list-task text-emerald-600"></i>
                    {{ __('Daily Activities') }}
                </h3>
            </div>

            {{-- Desktop: table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">{{ __('Day') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">{{ __('Date') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Activities / Tasks') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-56">{{ __('Attachment') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($dayNames as $i => $dayName)
                            @php
                                $currentDate = $start->copy()->addDays($i);
                                $isPast = $currentDate->isPast() || $currentDate->isToday();
                                $dayActivities = collect($existingActivities)->firstWhere('day', $dayName);
                            @endphp
                            <tr class="{{ !$isPast ? 'opacity-50' : '' }}">
                                <td class="px-3 py-2 text-sm font-medium text-gray-900 whitespace-nowrap">{{ __($dayName) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600 whitespace-nowrap">
                                    {{ $currentDate->format('M d, Y') }}
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @if ($weeklyJournal->isEditable())
                                        <textarea x-on:blur="saveActivities()"
                                                  x-model="activities[{{ $i }}].tasks"
                                                  rows="4"
                                                  x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                                  @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-xs resize-none overflow-hidden"
                                                  placeholder="{{ __('Describe tasks completed...') }}"></textarea>
                                    @else
                                        <p class="text-gray-700 whitespace-pre-wrap">{{ $dayActivities['tasks'] ?? '-' }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm">
                                    @if ($weeklyJournal->isEditable())
                                        <div class="flex flex-col gap-1.5">
                                            <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-900">
                                                <i class="bi bi-upload"></i>
                                                <span>{{ __('Choose file') }}</span>
                                                <input type="file" x-on:change="uploadFile('{{ $dayName }}', $event)" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            </label>
                                            <template x-for="file in fileList('{{ $dayName }}')" :key="file.file_path">
                                                <div class="flex items-center justify-between gap-1 rounded bg-gray-50 px-2 py-1">
                                                    <a :href="file.url" target="_blank"
                                                       class="flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-900 truncate max-w-[180px]">
                                                        <template x-if="file.file_name.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                                            <img :src="file.url" class="w-8 h-8 rounded object-cover shrink-0" loading="lazy">
                                                        </template>
                                                        <template x-if="!file.file_name.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                                            <i class="bi bi-paperclip shrink-0"></i>
                                                        </template>
                                                        <span class="truncate" x-text="file.file_name"></span>
                                                    </a>
                                                    <button @click="removeFile(file)" class="text-red-400 hover:text-red-600 shrink-0">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    @else
                                        @php
                                            $dayFile = collect($existingFiles)->firstWhere('day', $dayName);
                                        @endphp
                                        @if ($dayFile)
                                            <a href="{{ route('student.weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $dayFile['day']]) }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-emerald-700 hover:text-emerald-900">
                                                <i class="bi bi-paperclip"></i>
                                                <span class="truncate max-w-[180px]">{{ $dayFile['file_name'] }}</span>
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: card-per-day --}}
            <div class="block md:hidden space-y-3">
                @foreach ($dayNames as $i => $dayName)
                    @php
                        $currentDate = $start->copy()->addDays($i);
                        $isPast = $currentDate->isPast() || $currentDate->isToday();
                        $dayActivities = collect($existingActivities)->firstWhere('day', $dayName);
                    @endphp
                    <div class="rounded-lg border border-gray-200 p-3 {{ !$isPast ? 'opacity-50' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ __($dayName) }}</p>
                                <p class="text-xs text-gray-500">{{ $currentDate->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="mb-2">
                            @if ($weeklyJournal->isEditable())
                                <textarea x-on:blur="saveActivities()"
                                          x-model="activities[{{ $i }}].tasks"
                                          rows="4"
                                          x-init="$nextTick(() => { $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px' })"
                                          @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-xs resize-none overflow-hidden"
                                          placeholder="{{ __('Describe tasks completed...') }}"></textarea>
                            @else
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $dayActivities['tasks'] ?? '-' }}</p>
                            @endif
                        </div>
                        <div>
                            @if ($weeklyJournal->isEditable())
                                <div class="flex flex-col gap-1.5">
                                    <label class="cursor-pointer inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-900">
                                        <i class="bi bi-upload"></i>
                                        <span>{{ __('Choose file') }}</span>
                                        <input type="file" x-on:change="uploadFile('{{ $dayName }}', $event)" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    </label>
                                    <template x-for="file in fileList('{{ $dayName }}')" :key="file.file_path">
                                        <div class="flex items-center justify-between gap-1 rounded bg-gray-50 px-2 py-1">
                                            <a :href="file.url" target="_blank"
                                               class="flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-900 truncate max-w-[180px]">
                                                <template x-if="file.file_name.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                                    <img :src="file.url" class="w-8 h-8 rounded object-cover shrink-0" loading="lazy">
                                                </template>
                                                <template x-if="!file.file_name.match(/\.(jpg|jpeg|png|gif|webp)$/i)">
                                                    <i class="bi bi-paperclip shrink-0"></i>
                                                </template>
                                                <span class="truncate" x-text="file.file_name"></span>
                                            </a>
                                            <button @click="removeFile(file)" class="text-red-400 hover:text-red-600 shrink-0">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            @else
                                @php
                                    $dayFile = collect($existingFiles)->firstWhere('day', $dayName);
                                @endphp
                                @if ($dayFile)
                                    @php $isImage = (bool) preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $dayFile['file_name'] ?? ''); @endphp
                                    <a href="{{ route('student.weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $dayFile['day']]) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs text-emerald-700 hover:text-emerald-900">
                                        @if ($isImage)
                                            <img src="{{ route('student.weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $dayFile['day']]) }}" class="w-8 h-8 rounded object-cover shrink-0" loading="lazy">
                                        @else
                                            <i class="bi bi-paperclip"></i>
                                        @endif
                                        <span class="truncate max-w-[180px]">{{ $dayFile['file_name'] }}</span>
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </x-page-card>

        {{-- Supervisor --}}
        <x-page-card compact>
            <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                <i class="bi bi-person-badge text-blue-600"></i>
                {{ __('Supervisor') }}
            </h3>
            <p class="text-sm text-gray-700">
                {{ $weeklyJournal->supervisor_name ?? __('Not assigned') }}
            </p>
            @if ($weeklyJournal->deployment?->company)
                <p class="text-xs text-gray-400 mt-1">
                    <i class="bi bi-info-circle me-1"></i>{{ __('Auto-filled from your assigned company contact.') }}
                </p>
            @endif
        </x-page-card>

        {{-- Remarks --}}
        @if ($weeklyJournal->remarks)
            <x-page-card compact>
                <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                    <i class="bi bi-chat-square-text text-amber-600"></i>
                    {{ __('Instructor Remarks') }}
                </h3>
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                    <p class="whitespace-pre-wrap">{{ $weeklyJournal->remarks }}</p>
                </div>
            </x-page-card>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="/student/weekly-journals"
               class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900">
                <i class="bi bi-arrow-left"></i> {{ __('Back to Journals') }}
            </a>

            <div class="flex items-center gap-2">
                @if ($weeklyJournal->isEditable())
                    <div x-data="{ submitOpen: false }" class="inline">
                        <button type="button" @click="submitOpen = true"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                            <i class="bi bi-send me-1"></i>{{ __('Submit for Review') }}
                        </button>
                        <template x-teleport="body">
                            <div x-show="submitOpen" x-cloak
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                                 @keydown.escape.window="submitOpen = false">
                                <div class="absolute inset-0 bg-gray-900/50" @click="submitOpen = false"></div>
                                 <div class="relative z-10 w-full max-w-md rounded-xl border border-gray-200 bg-white p-5 shadow-xl"
                                      role="alertdialog" aria-modal="true" @click.stop>
                                     <div class="flex items-center justify-between">
                                         <h3 class="text-base font-semibold text-gray-900">{{ __('Submit for Review?') }}</h3>
                                         <button type="button" @click="submitOpen = false"
                                                 class="text-gray-400 hover:text-gray-600 transition-colors">
                                             <i class="bi bi-x-lg text-sm"></i>
                                         </button>
                                     </div>
                                    <p class="mt-2 text-sm text-gray-600">{{ __('You will no longer be able to edit this journal entry.') }}</p>
                                    <div class="mt-5 flex justify-end gap-2">
                                        <button type="button" @click="submitOpen = false"
                                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                            {{ __('Cancel') }}
                                        </button>
                                        <form method="POST" action="/student/weekly-journals/{{ $weeklyJournal->id }}/submit" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                                <i class="bi bi-send me-1"></i>{{ __('Submit') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                @elseif ($weeklyJournal->isSubmitted())
                    <p class="text-sm text-amber-700 bg-amber-50 px-3 py-1.5 rounded-md">
                        <i class="bi bi-hourglass-split me-1"></i>{{ __('Awaiting instructor review') }}
                    </p>
                @elseif ($weeklyJournal->isReviewed())
                    <p class="text-sm text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-md">
                        <i class="bi bi-check-circle me-1"></i>{{ __('Reviewed by instructor') }}
                    </p>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('weeklyJournalEditor', (config) => ({
                journalId: config.journalId,
                activities: config.activities,
                files: config.files,
                canEdit: config.canEdit,
                csrf: config.csrf,
                baseUrl: config.baseUrl,
                saving: false,
                lastSaved: null,
                saveTimer: null,

                init() {
                    const dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const baseDate = new Date('{{ $weeklyJournal->week_start_date->format('Y-m-d') }}');
                    dayNames.forEach((day, i) => {
                        if (!this.activities.find(a => a.day === day)) {
                            const d = new Date(baseDate);
                            d.setDate(d.getDate() + i);
                            this.activities.push({
                                day: day,
                                date: d.toISOString().split('T')[0],
                                tasks: '',
                            });
                        }
                    });
                },

                fileList(day) {
                    return this.files.filter(f => f.day === day);
                },

                saveActivities() {
                    if (!this.canEdit) return;
                    if (this.saveTimer) clearTimeout(this.saveTimer);
                    this.saveTimer = setTimeout(() => {
                        this.saving = true;
                        fetch(this.baseUrl + '/activities', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrf,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ activities: this.activities }),
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.saving = false;
                            this.lastSaved = Date.now();
                        })
                        .catch(() => { this.saving = false; });
                    }, 600);
                },

                uploadFile(day, event) {
                    if (!this.canEdit || !event.target.files[0]) return;
                    const formData = new FormData();
                    formData.append('day', day);
                    formData.append('file', event.target.files[0]);

                    this.saving = true;
                    fetch(this.baseUrl + '/files', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.file) {
                                this.files.push(data.file);
                            }
                            this.saving = false;
                            this.lastSaved = Date.now();
                            if (window.showToast) window.showToast('{{ __('File uploaded.') }}', 'success', 2000);
                        })
                        .catch(() => { this.saving = false; });
                    event.target.value = '';
                },

                removeFile(file) {
                    if (!this.canEdit) return;
                    this.saving = true;
                    fetch(this.baseUrl + '/files', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ day: file.day, file_path: file.file_path }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.saved) {
                            this.files = this.files.filter(f => f.file_path !== file.file_path);
                        }
                        this.saving = false;
                        this.lastSaved = Date.now();
                        if (window.showToast) window.showToast('{{ __('File removed.') }}', 'success', 2000);
                    })
                    .catch(() => { this.saving = false; });
                },
            }));
        });
    </script>
    @endpush
</x-app-layout>
