@props(['title', 'items', 'first' => false])
@php
    $items = $items ?? [];
    $first = $first ?? false;
@endphp
@if (!empty($items))
    <div class="{{ $first ? '' : 'mt-3' }} space-y-1">
        <p class="px-2 pt-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $title }}</p>
        @foreach ($items as $item)
            @if (isset($item['children']))
                @foreach ($item['children'] as $child)
                    <a href="{{ $child['url'] }}"
                       class="block px-2 py-1 text-sm {{ $child['active'] ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                        <i class="{{ $child['icon'] }} me-2"></i>{{ $child['label'] }}
                        @if (isset($child['badge']))
                            <span class="ml-1 inline-flex min-w-4 h-4 px-1 items-center justify-center rounded-full bg-emerald-600 text-white text-[9px] font-semibold">
                                {{ $child['badge'] > 99 ? '99+' : $child['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            @else
                <a href="{{ $item['url'] }}"
                   class="block px-2 py-1 text-sm {{ $item['active'] ? 'font-semibold text-emerald-700' : 'text-gray-700' }}">
                    <i class="{{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                    @if (isset($item['badge']))
                        <span class="ml-1 inline-flex min-w-4 h-4 px-1 items-center justify-center rounded-full bg-emerald-600 text-white text-[9px] font-semibold">
                            {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>
@endif