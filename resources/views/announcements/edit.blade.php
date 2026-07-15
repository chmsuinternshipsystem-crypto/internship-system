<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Communication</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Announcement</h2>
                <p class="text-sm text-gray-500">Update this announcement's content and visibility.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('announcements.update', $announcement) }}" method="POST">
                        @method('PUT')
                        @include('announcements._form', ['announcement' => $announcement, 'submitLabel' => __('Update')])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

