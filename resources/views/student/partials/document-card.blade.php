@php
    $wf = (string) ($current?->workflow_status ?? '');
    $dbStatus = (string) ($current?->status ?? 'Missing');
    $hasFile = filled($current?->file_path);

    if (! $current) {
        $statusText = __('Missing');
        $statusIcon = 'bi-exclamation-circle';
        $statusClass = 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
        $borderClass = 'border-l-rose-500';
    } elseif ($wf === 'completed' || $dbStatus === 'Submitted') {
        $statusText = __('Verified');
        $statusIcon = 'bi-check-circle-fill';
        $statusClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
        $borderClass = 'border-l-emerald-500';
    } elseif (in_array($wf, ['for_revision', 'rejected'], true) || $dbStatus === 'Missing') {
        $statusText = __('Needs resubmission');
        $statusIcon = 'bi-arrow-repeat';
        $statusClass = 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
        $borderClass = 'border-l-rose-500';
    } elseif ($hasFile || $dbStatus === 'Pending') {
        $statusText = __('In review');
        $statusIcon = 'bi-clock';
        $statusClass = 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
        $borderClass = 'border-l-amber-500';
    } else {
        $statusText = $dbStatus;
        $statusIcon = 'bi-file-earmark';
        $statusClass = 'bg-gray-50 text-gray-700 ring-1 ring-gray-200';
        $borderClass = 'border-l-gray-400';
    }

    $submittedAt = $current?->submitted_at;
    if (! $submittedAt && $hasFile) {
        $submittedAt = $current?->updated_at;
    }
    $deadlineAt = $doc->submission_deadline_at;
    $actionCount = $current?->actions->count() ?? 0;

    $nowTs = now();

    $hasDeadline = (bool) $deadlineAt;
    $isOverdue = $hasDeadline && ! $submittedAt && $deadlineAt->lt($nowTs);
    $isLateSubmitted = $hasDeadline && $submittedAt && $submittedAt->gt($deadlineAt);
    $isDueToday = $hasDeadline && ! $submittedAt && $deadlineAt->isSameDay($nowTs);
    $isDueSoon = $hasDeadline && ! $submittedAt && $deadlineAt->isFuture() && $deadlineAt->diffInHours($nowTs) <= 48;
    $isOnTime = $hasDeadline && $submittedAt && ! $isLateSubmitted;

    $downloadUrl = $hasFile ? route('student.documents.download', $current) : null;
    $fileUrl = $hasFile ? Storage::disk('public')->url($current->file_path) : null;
    $ext = $hasFile ? strtolower(pathinfo($current->file_path, PATHINFO_EXTENSION)) : '';
    $isPdf = $hasFile && $ext === 'pdf';
    $isDocx = $hasFile && $ext === 'docx';

    $cardId = 'doc-card-'.$doc->id;
    $indicatorId = 'doc-upload-indicator-'.$doc->id;

    $cardActions = $current?->actions->map(fn ($a) => [
        'action' => $a->action,
        'actor' => $a->actor?->name ?? __('System'),
        'actor_role' => $a->actor_role,
        'status' => $a->to_status,
        'acted_at' => $a->acted_at?->format('M d, Y h:i A'),
        'note' => $a->note,
    ])->values() ?? [];
@endphp

<div id="{{ $cardId }}" x-data='{ activityOpen: false, previewOpen: false, activities: @json($cardActions), fileSelected: false, fileName: "" }'
    class="group relative border-l-4 {{ $borderClass }} rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5">

    {{-- Card body --}}
    <div class="p-4 sm:p-5">
        {{-- Top row: icon + name + badges --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0 flex-1">
                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-50 ring-1 ring-gray-200 text-gray-600">
                    <i class="bi bi-file-text text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $doc->name }}</h3>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[11px] font-semibold text-rose-600 ring-1 ring-rose-200">{{ __('Required') }}</span>
                    </div>
                    @if ($doc->company)
                        <p class="mt-0.5 text-xs text-indigo-600 truncate">{{ $doc->company->name }}</p>
                    @endif
                </div>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                <i class="bi {{ $statusIcon }}"></i>
                {{ $statusText }}
            </span>
        </div>

        {{-- Info strip: deadline + submission --}}
        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs">
            {{-- Deadline --}}
            <span class="inline-flex items-center gap-1.5 {{ $isOverdue ? 'text-rose-600 font-semibold' : ($isDueToday ? 'text-amber-600 font-semibold' : 'text-gray-500') }}">
                <i class="bi bi-calendar3"></i>
                @if ($isOverdue)
                    {{ __('Overdue — was due :date', ['date' => $deadlineAt->format('M d, Y')]) }}
                @elseif ($isLateSubmitted)
                    {{ __('Submitted late on :date', ['date' => $submittedAt->format('M d, Y')]) }}
                @elseif ($isDueToday)
                    {{ __('Due today at :time', ['time' => $deadlineAt->format('h:i A')]) }}
                @elseif ($isDueSoon)
                    {{ __('Due :date (soon)', ['date' => $deadlineAt->format('M d, Y')]) }}
                @elseif ($hasDeadline && $deadlineAt->isFuture())
                    {{ __('Due :date', ['date' => $deadlineAt->format('M d, Y')]) }}
                @elseif ($hasDeadline && $isOnTime)
                    {{ __('Submitted on :date', ['date' => $submittedAt->format('M d, Y')]) }}
                @else
                    {{ __('No deadline set') }}
                @endif
            </span>

            {{-- Submission --}}
            @if ($hasFile)
                <span class="inline-flex items-center gap-1.5 text-emerald-600">
                    <i class="bi bi-paperclip"></i>
                    <span class="truncate max-w-[180px]">{{ basename((string) $current->file_path) }}</span>
                    @if ($isPdf || $isDocx)
                        <button type="button" @click="previewOpen = true"
                            class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            {{ __('View') }}
                        </button>
                    @else
                        <a href="{{ route('student.documents.download', $current) }}" target="_blank" rel="noopener noreferrer"
                            class="font-semibold text-emerald-700 hover:text-emerald-800 hover:underline">
                            {{ __('View') }}
                        </a>
                    @endif
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-gray-400">
                    <i class="bi bi-paperclip"></i>
                    {{ __('Not uploaded yet') }}
                </span>
            @endif
        </div>

        {{-- Action row --}}
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3">
            {{-- Upload form --}}
            <form method="POST" action="{{ route('student.documents.upload', $doc) }}" enctype="multipart/form-data"
                hx-post="{{ route('student.documents.upload', $doc) }}" hx-encoding="multipart/form-data"
                hx-target="#{{ $cardId }}" hx-swap="outerHTML" hx-indicator="#{{ $indicatorId }}"
                hx-disabled-elt="#{{ $cardId }} button, #{{ $cardId }} input[type='file']"
                class="flex flex-1 flex-wrap items-center gap-2">
                @csrf
                <input type="file" name="file" required accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    @change="fileSelected = !!$event.target.files.length; fileName = fileSelected ? $event.target.files[0].name : ''"
                    class="block w-full sm:max-w-[200px] rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 file:mr-2 file:rounded-md file:border-0 file:bg-emerald-50 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 transition-colors {{ ! empty($uploadError) ? 'border-rose-400 ring-1 ring-rose-200' : '' }}">
                <button type="submit"
                    :class="fileSelected ? 'inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50 shadow-[0_0_0_2px_rgba(5,150,105,0.3)]' : 'inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50'">
                    <i class="bi bi-upload"></i>
                    {{ $current?->file_path ? __('Replace') : __('Upload') }}
                </button>
                <template x-if="fileSelected">
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-medium truncate max-w-[160px]">
                        <i class="bi bi-file-earmark-check"></i>
                        <span x-text="fileName"></span>
                    </span>
                </template>
                <div id="{{ $indicatorId }}" class="htmx-indicator inline-flex items-center gap-1.5 text-xs text-gray-500">
                    <i class="bi bi-arrow-repeat animate-spin"></i>
                    {{ __('Uploading...') }}
                </div>
            </form>

            {{-- Activity button --}}
            @if ($actionCount > 0)
                <button type="button" @click="activityOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-800">
                    <i class="bi bi-activity"></i>
                    {{ __('Activity') }}
                    <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600">{{ $actionCount }}</span>
                </button>
            @endif
        </div>

        {{-- Upload error --}}
        @if (! empty($uploadError))
            <div class="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                <i class="bi bi-exclamation-circle mr-1"></i>
                {{ $uploadError }}
            </div>
        @endif
    </div>

    {{-- Activity slide-over drawer --}}
    @if ($actionCount > 0)
        <template x-teleport="body">
            <div x-show="activityOpen" 
                x-cloak
                class="fixed inset-0 z-50 overflow-hidden"
                @keydown.escape.window="activityOpen = false">
                {{-- Backdrop --}}
                <div x-show="activityOpen"
                    x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                    @click="activityOpen = false">
                </div>
                {{-- Panel --}}
                <div x-show="activityOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl"
                    @click.away="activityOpen = false">
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Document Activity') }}</p>
                            <h3 class="text-base font-semibold text-gray-900 truncate mt-0.5">{{ $doc->name }}</h3>
                        </div>
                        <button type="button" @click="activityOpen = false"
                            class="ml-4 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
                            <i class="bi bi-x-lg text-sm"></i>
                        </button>
                    </div>
                    {{-- Timeline --}}
                    <div class="overflow-y-auto px-5 py-5" style="max-height: calc(100vh - 80px);">
                        <div class="relative">
                            {{-- Vertical line --}}
                            <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-gray-200"></div>
                            {{-- Entries --}}
                            <template x-for="(act, idx) in activities" :key="idx">
                                <div class="relative flex gap-4 pb-6 last:pb-0">
                                    {{-- Dot --}}
                                    <div class="relative z-10 mt-1 flex shrink-0 items-center justify-center">
                                        <div class="h-5 w-5 rounded-full border-2 border-white bg-gray-100 ring-1 ring-gray-200 flex items-center justify-center"
                                            :class="{
                                                'bg-emerald-500 ring-emerald-300': act.action === 'approved' || act.action === 'completed',
                                                'bg-amber-500 ring-amber-300': act.action === 'submitted' || act.action === 'pending',
                                                'bg-rose-500 ring-rose-300': act.action === 'rejected' || act.action === 'for_revision',
                                                'bg-blue-500 ring-blue-300': act.action === 'forwarded' || act.action === 'received'
                                            }">
                                            <i class="bi text-[8px] text-white"
                                                :class="{
                                                    'bi-check': act.action === 'approved' || act.action === 'completed',
                                                    'bi-send': act.action === 'submitted',
                                                    'bi-x': act.action === 'rejected',
                                                    'bi-arrow-repeat': act.action === 'for_revision',
                                                    'bi-arrow-right': act.action === 'forwarded' || act.action === 'received',
                                                    'bi-circle': !['approved','completed','submitted','rejected','for_revision','forwarded','received'].includes(act.action)
                                                }"></i>
                                        </div>
                                    </div>
                                    {{-- Content --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                            <span class="text-sm font-semibold text-gray-900" x-text="act.actor"></span>
                                            <span class="text-xs font-medium text-gray-500 capitalize" x-text="act.action?.replace(/_/g, ' ')"></span>
                                        </div>
                                        <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            <span x-text="act.acted_at"></span>
                                            <template x-if="act.status">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">
                                                    <span x-text="act.status?.replace(/_/g, ' ')"></span>
                                                </span>
                                            </template>
                                        </div>
                                        <template x-if="act.note">
                                            <div class="mt-1.5 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600 border border-gray-100" x-text="act.note"></div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            {{-- Empty state --}}
                            <template x-if="activities.length === 0">
                                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                                    <i class="bi bi-inbox text-2xl"></i>
                                    <p class="mt-2 text-sm font-medium">{{ __('No activity recorded yet') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- Preview slide-over drawer --}}
    @if ($hasFile && ($isPdf || $isDocx))
        <template x-teleport="body">
            <div x-show="previewOpen"
                x-cloak
                class="fixed inset-0 z-50 overflow-hidden"
                @keydown.escape.window="previewOpen = false">
                {{-- Backdrop --}}
                <div x-show="previewOpen"
                    x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
                    @click="previewOpen = false">
                </div>
                {{-- Panel --}}
                <div x-show="previewOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="absolute inset-y-0 right-0 w-full max-w-4xl bg-white shadow-2xl flex flex-col"
                    @click.away="previewOpen = false">
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Document Preview') }}</p>
                            <h3 class="text-lg font-bold text-gray-900 truncate mt-0.5">{{ $doc->name }}</h3>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('student.documents.download', $current) }}" target="_blank"
                                class="inline-flex items-center rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                <i class="bi bi-download me-1.5"></i>{{ __('Download') }}
                            </a>
                            <button type="button" @click="previewOpen = false"
                                class="ml-2 flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    {{-- Preview body --}}
                    <div class="flex-1 flex overflow-hidden">
                        @if ($isPdf)
                            <div class="flex-1 flex flex-col" x-data="pdfPreview(@js($downloadUrl), @js($fileUrl))">
                                <template x-if="loading">
                                    <div class="flex items-center justify-center flex-1 p-6">
                                        <div class="text-center">
                                            <div class="w-10 h-10 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                            <p class="text-sm text-gray-500">{{ __('Loading document...') }}</p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="error">
                                    <div class="flex items-center justify-center flex-1 p-6">
                                        <div class="text-center max-w-md">
                                            <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
                                                <i class="bi bi-file-earmark-text text-4xl text-amber-500"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('Could not preview this file') }}</h3>
                                            <p class="text-sm text-gray-500 mb-6" x-text="error"></p>
                                            <a :href="downloadUrl" target="_blank"
                                                class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                                                <i class="bi bi-download me-2"></i>{{ __('Download File') }}
                                            </a>
                                        </div>
                                    </div>
                                </template>
                                <iframe x-ref="pdfFrame" class="w-full h-full border-0 flex-1" title="{{ __('Document preview') }}"></iframe>
                            </div>
                        @elseif ($isDocx)
                            <div class="flex-1 flex flex-col" x-data="docxPreview(@js($downloadUrl), @js($fileUrl))">
                                <div class="flex-1 relative">
                                    <div x-show="loading"
                                        class="absolute inset-0 z-10 flex items-center justify-center bg-white p-6">
                                        <div class="text-center">
                                            <div class="w-10 h-10 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                            <p class="text-sm text-gray-500">{{ __('Loading document...') }}</p>
                                        </div>
                                    </div>
                                    <div x-show="error"
                                        class="absolute inset-0 z-10 flex items-center justify-center bg-white p-6">
                                        <div class="text-center max-w-md">
                                            <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
                                                <i class="bi bi-file-earmark-text text-4xl text-amber-500"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('Could not preview this file') }}</h3>
                                            <p class="text-sm text-gray-500 mb-6" x-text="error"></p>
                                            <a :href="downloadUrl" target="_blank"
                                                class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                                                <i class="bi bi-download me-2"></i>{{ __('Download File') }}
                                            </a>
                                        </div>
                                    </div>
                                    <div x-ref="docxContainer"
                                        class="absolute inset-0"
                                        style="overflow-y: auto !important;"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
