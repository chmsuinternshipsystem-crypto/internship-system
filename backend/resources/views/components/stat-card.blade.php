@props([
    'label',
    'value',
    'sub' => null,
    'color' => '#3b82f6',
    'icon' => 'bi-people-fill',
    'link' => null,
    'linkText' => null,
])

<div class="stat-card" style="border-left-color: {{ $color }}">
    <div class="stat-card-body">
        <div>
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-number">{{ $value }}</div>
            @if ($sub)
                <div class="stat-sub">{{ $sub }}</div>
            @endif
        </div>
        <div class="stat-icon" style="background: {{ $color }}">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
    @if ($link)
        <a href="{{ $link }}" class="stat-footer-link">
            {{ $linkText ?? __('View details') }} <i class="bi bi-arrow-right ms-1"></i>
        </a>
    @endif
</div>
