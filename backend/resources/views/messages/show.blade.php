<x-app-layout>
    @php
        $isStudentPortal = $isStudentPortal ?? false;
        $indexRoute = $isStudentPortal ? 'student.messages.index' : 'messages.index';
    @endphp
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Communication') }}</p>
            <h2 class="font-semibold text-xl text-gray-900 leading-tight truncate max-w-xl">{{ $thread->subject }}</h2>
            <p class="text-sm text-gray-500 flex items-center gap-1.5">
                <i class="bi bi-people"></i>
                {{ __('Participants:') }} {{ $thread->participantNamesLabel() }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8" id="message-conversation-panel">
            @include('messages.partials.conversation', [
                'thread' => $thread,
                'isStudentPortal' => $isStudentPortal,
            ])
        </div>
    </div>
</x-app-layout>
