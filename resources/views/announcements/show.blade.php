<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Announcements'), 'url' => route('announcements.index')],
    ['label' => $announcement->title],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Communication</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Announcement Details</h2>
                <p class="text-sm text-gray-500">View the full announcement content.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $announcement->title }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ __('Visible to:') }} {{ $announcement->visible_to_role ?? __('All') }}
                        </p>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-gray-500">{{ __('Body') }}</span>
                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                            {{ $announcement->body }}
                        </p>
                    </div>

                    <div class="space-y-1 text-sm text-gray-600">
                        <div>
                            <span class="font-semibold">{{ __('Author:') }}</span>
                            {{ $announcement->author?->name ?? '-' }}
                        </div>
                        <div>
                            <span class="font-semibold">{{ __('Created at:') }}</span>
                            {{ $announcement->created_at?->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end space-x-2">
                        <a href="{{ route('announcements.edit', $announcement) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ route('announcements.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

