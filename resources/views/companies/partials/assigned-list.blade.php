<div x-data="{ removeId: null }">
    @if ($assignedDeployments->isNotEmpty())
        <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg text-sm">
            @foreach ($assignedDeployments as $dep)
                <li class="flex items-center justify-between gap-2 px-3 py-2.5 hover:bg-gray-50">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $dep->student?->name ?? __('Unknown') }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $dep->student?->student_number ?? '' }}
                            @if ($dep->student?->section)
                                &middot; {{ __('Section') }} {{ $dep->student->section }}
                            @endif
                            &middot; <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold
                                @if($dep->status === 'active') bg-emerald-100 text-emerald-700
                                @elseif($dep->status === 'completed') bg-blue-100 text-blue-700
                                @else bg-gray-100 text-gray-600 @endif">
                                {{ Str::headline($dep->status) }}
                            </span>
                        </p>
                    </div>
                    @if ($dep->status === 'pending')
                        <button type="button" @click="removeId = {{ $dep->id }}"
                                class="text-red-400 hover:text-red-600 transition-colors p-1 text-sm" title="{{ __('Remove') }}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="mt-3 text-xs">
            @include('partials.htmx-pagination', ['paged' => $assignedDeployments, 'hxTarget' => '#assigned-list-mount'])
        </div>

        {{-- Confirmation modal --}}
        <template x-teleport="body">
            <div x-show="removeId" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                 @keydown.escape.window="removeId = null"
                 @click.self="removeId = null">
                <div class="bg-white rounded-xl shadow-xl max-w-sm w-full mx-4 p-6">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Confirm Removal') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Remove this student from :company?', ['company' => $company->name]) }}
                    </p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="removeId = null"
                                class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <form method="POST" :action="'{{ route('companies.students.detach', $company) }}'">
                            @csrf
                            <input type="hidden" name="deployment_id" :value="removeId">
                            <button type="submit"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                                {{ __('Remove') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    @else
        <p class="text-sm text-gray-500 py-4 text-center">{{ __('No students assigned to this company yet.') }}</p>
    @endif
</div>
