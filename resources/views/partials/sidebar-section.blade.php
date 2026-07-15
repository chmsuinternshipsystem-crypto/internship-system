@props(['title', 'items', 'first' => false])
@php
    $items = $items ?? [];
    $first = $first ?? false;
@endphp
@if (!empty($items))
    <div class="{{ $first ? '' : 'mt-3' }} space-y-1">
        <p class="px-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400">{{ $title }}</p>
        @foreach ($items as $item)
            @if (isset($item['children']))
                {{-- Submenu with nested items --}}
                <div class="space-y-1">
                    @foreach ($item['children'] as $child)
                        <a href="{{ $child['url'] }}"
                           class="nav-link {{ $child['active'] ? 'active' : '' }}"
                           {{ isset($child['badge']) ? 'badge="' . $child['badge'] . '"' : '' }}>
                            <i class="{{ $child['icon'] }} me-2"></i>{{ $child['label'] }}
                            @if (isset($child['badge']))
                                <span class="ml-auto inline-flex min-w-5 h-5 px-1.5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px] font-semibold">
                                    {{ $child['badge'] > 99 ? '99+' : $child['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @else
                <a href="{{ $item['url'] }}"
                   class="nav-link {{ $item['active'] ? 'active' : '' }}"
                   {{ isset($item['badge']) ? 'badge="' . $item['badge'] . '"' : '' }}>
                    <i class="{{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                    @if (isset($item['badge']))
                        <span class="ml-auto inline-flex min-w-5 h-5 px-1.5 items-center justify-center rounded-full bg-emerald-600 text-white text-[10px] font-semibold">
                            {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>
@endif