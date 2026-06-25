@props(['items' => []])

@if (count($items) > 0)
    <nav class="flex items-center gap-1.5 text-xs text-gray-500 mb-2" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition-colors">
            <i class="bi bi-house-door"></i>
        </a>
        @foreach ($items as $item)
            <i class="bi bi-chevron-right text-gray-300" style="font-size: 10px;"></i>
            @if (isset($item['url']))
                <a href="{{ $item['url'] }}" class="hover:text-emerald-600 transition-colors truncate max-w-[180px]">
                    {{ $item['label'] }}
                </a>
            @else
                <span class="text-gray-800 font-medium truncate max-w-[200px]">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
