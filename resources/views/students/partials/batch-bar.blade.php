@if ($canManage)
<div x-cloak x-show="selected.length > 0 || selectAllMatching"
     class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-2.5 mb-3 transition-all flex items-center justify-between">
    <span class="text-sm font-medium text-emerald-900">
        <template x-if="!selectAllMatching">
            <span x-text="selected.length + ' selected'"></span>
        </template>
        <template x-if="selectAllMatching">
            <span x-text="(totalMatching - deselected.length) + ' selected'"></span>
        </template>
    </span>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('batch.students') }}" class="inline-flex items-center gap-2"
              x-data="{ confirming: false }"
              @submit.prevent="confirming = true"
              id="batch-assign-form">
            @csrf
            <input type="hidden" name="action" value="assign-instructor">
            <template x-if="selectAllMatching">
                <input type="hidden" name="select_all_matching" value="1">
            </template>
            <template x-if="selectAllMatching">
                <input type="hidden" name="filter_section" value="{{ $section ?? '' }}">
            </template>
            <template x-if="selectAllMatching">
                <input type="hidden" name="filter_search" value="{{ $search ?? '' }}">
            </template>
            <template x-if="selectAllMatching">
                <input type="hidden" name="filter_my_students" value="{{ ($myStudents ?? false) ? '1' : '' }}">
            </template>
            <template x-if="selectAllMatching">
                <template x-for="id in deselected" :key="'ex-' + id">
                    <input type="hidden" name="exclude_ids[]" :value="id">
                </template>
            </template>
            <template x-if="!selectAllMatching">
                <template x-for="id in selected" :key="'ai-' + id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </template>
            <select name="instructor_id" required class="text-sm border-gray-300 rounded-md">
                <option value="">{{ __('Assign instructor...') }}</option>
                @foreach ($instructors as $instructor)
                    <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary text-xs px-3 py-1.5">
                {{ __('Assign') }}
            </button>

            <template x-teleport="body">
                <div x-show="confirming" x-cloak
                     class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                     @keydown.escape.window="confirming = false">
                    <div class="absolute inset-0 bg-gray-900/50" @click="confirming = false"></div>
                    <div class="relative z-10 w-full max-w-sm rounded-xl border border-gray-200 bg-white p-5 shadow-xl" @click.stop>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Confirm Batch Assign') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Assign') }} <strong x-text="selectAllMatching ? (totalMatching - deselected.length) : selected.length"></strong> {{ __('student(s) to the selected instructor?') }}
                        </p>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="confirming = false"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button" @click="document.getElementById('batch-assign-form').submit()"
                                    class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                {{ __('Confirm') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </form>
        <button @click="clear()" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
            {{ __('Cancel') }}
        </button>
    </div>
</div>
@endif