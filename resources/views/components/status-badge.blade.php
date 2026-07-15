@props(['variant' => 'default'])
@php
$classes = match($variant ?? 'default') {
    'success', 'compliant', 'active', 'submitted' => 'bg-green-200 text-green-800',
    'warning', 'partial', 'pending' => 'bg-blue-200 text-blue-800',
    'danger', 'non_compliant', 'missing' => 'bg-red-200 text-red-800',
    'inactive' => 'bg-gray-200 text-gray-800',
    default => 'bg-gray-200 text-gray-800',
};
@endphp
<span {{ $attributes->merge(['class' => "px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$classes}"]) }}>
    {{ $slot }}
</span>
