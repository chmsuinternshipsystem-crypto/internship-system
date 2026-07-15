@props([
    'compact' => false,
    'padding' => null,
])
@php
    $innerClass = $compact ? 'page-card-inner page-card-inner--compact' : 'page-card-inner';
    if ($padding) $innerClass .= ' ' . $padding;
@endphp
<div {{ $attributes->merge(['class' => 'bg-white shadow-sm sm:rounded-lg border border-gray-100']) }}>
    <div class="{{ $innerClass }} text-gray-900">
        {{ $slot }}
    </div>
</div>
