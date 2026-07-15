@props([
    'variant' => 'primary', // primary, secondary, danger
    'size' => 'md', // sm, md, lg
    'type' => 'button',
    'href' => null,
])

@php
$base = 'inline-flex items-center justify-center font-semibold uppercase tracking-widest transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs rounded-md',
    'md' => 'px-4 py-2 text-xs rounded-lg',
    'lg' => 'px-6 py-3 text-sm rounded-lg',
];

$variants = [
    'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500 border border-transparent',
    'secondary' => 'bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-400 border border-gray-300',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 border border-transparent',
];

$class = trim("$base {$sizes[$size]} {$variants[$variant]}");
@endphp

@if ($href)
    <a href="{{ $href }}" class="{{ $class }}" {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $class }}" {{ $attributes }}>
        {{ $slot }}
    </button>
@endif
