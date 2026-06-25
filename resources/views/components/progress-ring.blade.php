@props(['percentage' => 0, 'size' => 100, 'strokeWidth' => 8])
@php
    $pct = min(100, max(0, (int) $percentage));
    $radius = ($size - $strokeWidth) / 2;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference * (1 - $pct / 100);
    $center = $size / 2;
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
    <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" class="text-gray-200"/>
    <circle cx="{{ $center }}" cy="{{ $center }}" r="{{ $radius }}" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" stroke-linecap="round"
        stroke-dasharray="{{ $circumference }}"
        stroke-dashoffset="{{ $circumference }}"
        class="text-emerald-500 transition-all duration-1000 ease-out"
        style="stroke-dashoffset: {{ $offset }}"/>
</svg>
