@props(['title' => null, 'actionHref' => null, 'actionLabel' => null])
<div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-2 {{ $title ? 'sm:justify-between' : 'sm:justify-end' }}">
    @if ($title)
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    @endif
    @if ($actionHref && $actionLabel)
        <a href="{{ $actionHref }}"
           class="inline-flex items-center gap-1.5 px-5 py-2.5 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2">
            <i class="bi bi-plus-lg"></i>
            {{ $actionLabel }}
        </a>
    @endif
    {{ $slot }}
</div>
