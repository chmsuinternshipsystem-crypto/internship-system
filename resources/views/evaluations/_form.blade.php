@csrf

<div class="space-y-5">
    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">{{ __('Quick guide') }}</p>
        <p class="mt-1 text-xs text-gray-600">{{ __('Choose the student, evaluator type, and score. Submission time is recorded automatically.') }}</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="student_id" class="block text-sm font-medium text-gray-700">
                {{ __('Student') }}
            </label>
            <select
                id="student_id"
                name="student_id"
                required
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm select2-autocomplete"
                data-placeholder="{{ __('Search student...') }}"
            >
                <option value="">{{ __('Select a student') }}</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $evaluation->student_id ?? '') == $student->id)>
                        {{ $student->student_number }} - {{ $student->name }}
                    </option>
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="evaluation_type" class="block text-sm font-medium text-gray-700">
                {{ __('Evaluation Type') }}
            </label>
            <select
                id="evaluation_type"
                name="evaluation_type"
                required
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
            >
                <option value="industry" @selected(old('evaluation_type', $evaluation->evaluation_type ?? 'industry') === 'industry')>
                    {{ __('Industry Supervisor') }}
                </option>
                <option value="school" @selected(old('evaluation_type', $evaluation->evaluation_type ?? 'industry') === 'school')>
                    {{ __('School Instructor') }}
                </option>
            </select>
            @error('evaluation_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="score" class="block text-sm font-medium text-gray-700">
                {{ __('Score (1-100)') }}
            </label>
            <input
                id="score"
                name="score"
                type="number"
                min="1"
                max="100"
                step="1"
                inputmode="numeric"
                required
                value="{{ old('score', $evaluation->score ?? '') }}"
                class="mt-1 block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                oninput="if(this.value===''){return;} const v=parseInt(this.value,10); this.value=Number.isNaN(v)?'':Math.max(1,Math.min(100,v));"
            />
            @error('score')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="comments" class="block text-sm font-medium text-gray-700">
            {{ __('Comments (optional)') }}
        </label>
        <textarea
            id="comments"
            name="comments"
            rows="5"
            maxlength="1000"
            class="mt-1 block w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        >{{ old('comments', $evaluation->comments ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 1000 characters.') }}</p>
        @error('comments')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4">
    <a href="{{ route('evaluations.index') }}"
       class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex items-center rounded-md border border-transparent px-4 py-2 font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>

