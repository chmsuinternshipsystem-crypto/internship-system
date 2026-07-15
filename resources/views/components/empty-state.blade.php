@props(['colspan' => 1, 'message' => null])
<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-8 text-center text-sm text-gray-500">
        {{ $message ?? __('No records found.') }}
    </td>
</tr>
