@php
    $isStudentPortal = $isStudentPortal ?? false;
    $indexRoute = $isStudentPortal ? 'student.messages.index' : 'messages.index';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Communication') }}</p>
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">{{ __('New Message') }}</h2>
            <p class="text-sm text-gray-500">
                {{ $isStudentPortal
                    ? __('Send a message to your instructor or chairperson.')
                    : __('Choose recipients and write your message.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="message-conversation-panel">
                @include('messages.partials.create-form', [
                'recipients' => $recipients,
                'studentAccountsForMessaging' => $studentAccountsForMessaging,
                'isStudentPortal' => $isStudentPortal,
                'preSelectedStudentIds' => $preSelectedStudentIds ?? [],
                'isHtmxPartial' => false,
            ])
        </div>
    </div>
    </div>
</x-app-layout>
