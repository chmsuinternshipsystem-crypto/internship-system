<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Transmittal') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Document Forwarding Batches') }}</h2>
            </div>
            @if ((auth()->user()?->role ?? '') === 'instructor')
                <a href="{{ route('document-forwarding.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    {{ __('New batch') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="space-y-4">
        @foreach ($batches as $batch)
            <x-page-card compact>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Batch #:id', ['id' => $batch->id]) }}</p>
                        <p class="text-xs text-gray-500">
                            {{ __('Status') }}: {{ ucfirst($batch->status) }}
                            · {{ __('Release') }}: {{ $batch->release_at?->format('M d, Y h:i A') ?? '—' }}
                            · {{ __('By') }}: {{ $batch->creator?->name ?? '—' }}
                        </p>
                    </div>
                    @if ((auth()->user()?->role ?? '') === 'instructor' && $batch->status !== 'released')
                        <form method="POST" action="{{ route('document-forwarding.release', $batch) }}">
                            @csrf
                            <button type="submit" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                {{ __('Release now') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-3 overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left">{{ __('Student') }}</th>
                                <th class="px-2 py-2 text-left">{{ __('Document') }}</th>
                                <th class="px-2 py-2 text-left">{{ __('Released') }}</th>
                                <th class="px-2 py-2 text-left">{{ __('Acknowledged') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($batch->items as $item)
                                <tr>
                                    <td class="px-2 py-2">{{ $item->student?->name ?? '—' }}</td>
                                    <td class="px-2 py-2">{{ $item->requiredDocument?->name ?? '—' }}</td>
                                    <td class="px-2 py-2">{{ $item->released_at?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td class="px-2 py-2">
                                        @if ($item->acknowledged_at)
                                            {{ $item->acknowledged_at->format('M d, Y h:i A') }}
                                        @elseif ((auth()->user()?->role ?? '') === 'instructor' && $batch->status === 'released')
                                            <form method="POST" action="{{ route('document-forwarding.acknowledge', $item) }}">
                                                @csrf
                                                <button type="submit" class="rounded border border-emerald-200 px-2 py-1 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50">{{ __('Mark received') }}</button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-page-card>
        @endforeach

        <div>@include('partials.htmx-pagination', ['paged' => $batches])</div>
    </div>
</x-app-layout>
