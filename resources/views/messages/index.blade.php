<x-app-layout>
    @php
        $isStudentPortal = $isStudentPortal ?? false;
        $createRoute = $isStudentPortal ? 'student.messages.create' : 'messages.create';
    @endphp
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Communication') }}</p>
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">{{ __('Messages') }}</h2>
            <p class="text-sm text-gray-500">
                {{ $isStudentPortal
                    ? __('Message your instructor or chairperson about internship concerns.')
                    : __('Coordinate with staff, instructors, and students.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 gap-6" x-data="{ search: '' }">
                {{-- LEFT: Inbox column --}}
                <div class="col-span-1">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ __('Inbox') }}</h3>
                        <button type="button"
                                hx-get="{{ route($createRoute) }}"
                                hx-target="#message-conversation-panel"
                                hx-swap="innerHTML"
                                hx-indicator="#conversation-loading"
                                class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                title="{{ __('New Message') }}">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                     <div id="message-inbox-column"
                          hx-trigger="refresh-inbox from:body, every 30s"
                          hx-get="{{ route($isStudentPortal ? 'student.messages.index' : 'messages.index') }}"
                          hx-target="#message-inbox-column"
                          hx-swap="innerHTML">
                        @include('messages.partials.inbox-column')
                    </div>
                </div>

                {{-- RIGHT: Content panel --}}
                <div class="col-span-2 relative min-h-[520px]" id="message-conversation-panel">
                    <style>
                        #conversation-loading { display: none; }
                        #conversation-loading.htmx-request { display: flex; }
                    </style>

                    <div id="conversation-loading" class="absolute inset-0 items-center justify-center bg-white/80 rounded-xl z-10">
                        <div class="flex items-center gap-3 text-gray-500">
                            <svg class="animate-spin h-5 w-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">{{ __('Loading...') }}</span>
                        </div>
                    </div>

                    {{-- Empty state --}}
                    <div id="message-empty-state">
                        @include('messages.partials.empty-state')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
