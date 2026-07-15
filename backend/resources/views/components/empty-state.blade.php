@props(['colspan' => 1, 'message' => null, 'icon' => 'bi-inbox', 'title' => null, 'action' => null])
<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-10 text-center">
        <div class="flex flex-col items-center justify-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50 border border-gray-100 mb-4">
                <i class="bi {{ $icon }} text-2xl text-gray-400"></i>
            </span>
            <strong class="text-sm font-semibold text-gray-700">{{ $title ?? __('No records found') }}</strong>
            @if ($message)
                <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ $message }}</p>
            @endif
            @if ($action)
                <div class="mt-4">{{ $action }}</div>
            @endif
        </div>
    </td>
</tr>
