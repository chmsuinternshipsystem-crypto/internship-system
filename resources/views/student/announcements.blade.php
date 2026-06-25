<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Student Portal') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Announcements') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Official notices from your coordinators and faculty.') }}</p>
        </div>
    </x-slot>

    @if (! empty($studentPortalLimited) && $studentPortalLimited)
        <div class="mx-auto max-w-3xl mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ __('Announcements are always available. Other portal areas open after deployment and mandatory documents are complete.') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-4">
        @forelse ($announcements as $announcement)
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                <div class="flex gap-4 px-5 py-4 sm:px-6">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                        <i class="bi bi-megaphone text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-base font-semibold text-gray-900">{{ $announcement->title }}</h3>
                        <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <i class="bi bi-calendar3"></i>
                                {{ $announcement->created_at?->format('M d, Y h:i A') }}
                            </span>
                            @if ($announcement->author)
                                <span class="text-gray-300">·</span>
                                <span class="inline-flex items-center gap-1">
                                    <i class="bi bi-person"></i>
                                    {{ $announcement->author->name }}
                                </span>
                            @endif
                        </p>
                        <div class="mt-3 text-sm leading-relaxed text-gray-700 whitespace-pre-line">{{ $announcement->body }}</div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center">
                <i class="bi bi-inbox text-3xl text-gray-300"></i>
                <p class="mt-3 text-sm font-medium text-gray-600">{{ __('No announcements yet.') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ __('Check back later for updates from your internship office.') }}</p>
            </div>
        @endforelse

        @if ($announcements->count() > 0)
            <div class="pt-2">
                @include('partials.htmx-pagination', ['paged' => $announcements])
            </div>
        @endif
    </div>
</x-app-layout>
