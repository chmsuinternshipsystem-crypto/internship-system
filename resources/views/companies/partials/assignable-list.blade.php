<form method="POST" action="{{ route('companies.students.attach', $company) }}"
      x-data="{ selected: [], allIds: [] }"
      x-init="allIds = {{ $students->pluck('id')->toJson() }}"
      @submit="if (selected.length > 0) { let o = document.getElementById('loadingOverlay'); if (o) o.style.display = 'flex'; }">
    @csrf
    <div class="space-y-2">
        @if ($students->isNotEmpty())
            <div class="flex items-center justify-between mb-2">
                <label class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                    <input type="checkbox" @change="selected = $event.target.checked ? [...allIds] : []"
                           :checked="selected.length === allIds.length && allIds.length > 0"
                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                    {{ __('Select all on this page') }}
                </label>
                <span class="text-xs text-gray-500" x-text="selected.length + ' selected'"></span>
            </div>
            <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg text-sm">
                @foreach ($students as $student)
                    <li class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                               @change="if($event.target.checked) { selected.push({{ $student->id }}); } else { selected = selected.filter(id => id !== {{ $student->id }}); }"
                               :checked="selected.includes({{ $student->id }})"
                               class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600 shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $student->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $student->student_number }}
                                &middot; {{ __('Section') }} {{ $student->section }}
                                @if ($student->assignedInstructor)
                                    &middot; {{ $student->assignedInstructor->name }}
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3">
                @include('partials.htmx-pagination', ['paged' => $students, 'hxTarget' => '#assignable-list-mount'])
            </div>
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <button type="submit" x-show="selected.length > 0" x-cloak
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                    <i class="bi bi-plus-circle"></i>
                    <span x-text="'{{ __('Assign Selected') }} (' + selected.length + ')'"></span>
                </button>
            </div>
        @else
            <p class="text-sm text-gray-500 py-4 text-center">{{ __('No assignable students found.') }}</p>
        @endif
    </div>
</form>
