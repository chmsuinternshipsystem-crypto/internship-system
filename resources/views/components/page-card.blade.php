@props([
    /** Tighter padding on list/report pages (tokens: --page-card-padding-*) */
    'compact' => false,
])
@php
    $innerClass = $compact ? 'page-card-inner page-card-inner--compact' : 'page-card-inner';
@endphp
<div {{ $attributes->merge(['class' => 'bg-white shadow-sm sm:rounded-lg']) }}>
    <div class="{{ $innerClass }} text-gray-900">
        {{ $slot }}
    </div>
</div>
