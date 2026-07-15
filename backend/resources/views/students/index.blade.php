<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Registry') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Students') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Manage BSIS internship student records, sections, and contact details.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="$canManage ? route('students.create') : null"
        actionLabel="{{ __('Create Student Profile') }}">
        @if($canManage)
            <a href="{{ route('students.import') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-wider bg-white hover:bg-gray-50 shadow-sm transition-colors">
                <i class="bi bi-upload"></i>
                {{ __('Import') }}
            </a>
        @endif
    </x-page-header>

    <x-page-card compact>
        <x-search-bar
            :action="route('students.index')"
            :value="$search"
            :placeholder="__('Student number, name, section, contact, or status...')"
            hxTarget="#students-table-mount"
            :showClear="$hasActiveFilters"
            sticky
        >
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5 text-xs font-medium" x-data="{
                status: @js($deploymentStatus ?? ''),
                filterStatus(val) {
                    this.status = val;
                    var params = new URLSearchParams();
                    if (val) params.set('deployment_status', val);
                    if (@js($section ?? '')) params.set('section', @js($section ?? ''));
                    if (@js($search ?? '')) params.set('search', @js($search ?? ''));
                    if (@js($myStudents ?? false)) params.set('my_students', '1');
                    var q = params.toString();
                    var url = '{{ route('students.index') }}' + (q ? '?' + q : '');
                    if (window.htmx) htmx.ajax('GET', url, {target: '#students-table-mount', swap: 'innerHTML', pushUrl: true});
                }
            }">
                <button @click="filterStatus('')" type="button" :class="status === '' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition-colors">{{ __('All') }}</button>
                <button @click="filterStatus('pending')" type="button" :class="status === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition-colors">{{ __('Pending') }}</button>
                <button @click="filterStatus('deployed')" type="button" :class="status === 'deployed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-3 py-1.5 rounded-md transition-colors">{{ __('Deployed') }}</button>
            </div>
            <span class="filter-label">{{ __('Section') }}</span>
            <select name="section" class="filter-select">
                <option value="">{{ __('All sections') }}</option>
                @foreach (['A', 'B', 'C', 'D'] as $sec)
                    <option value="{{ $sec }}" @selected(($section ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                @endforeach
            </select>
            @if ((auth()->user()?->role ?? '') === 'instructor')
            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                {{ __('My students') }}
            </label>
            @endif
            <input type="hidden" name="no_company" value="0">
            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                <input type="checkbox" name="no_company" value="1" @checked($noCompany ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                {{ __('No Company') }}
            </label>
            <input type="hidden" name="page" value="{{ request()->query('page', '') }}">
        </x-search-bar>
        <div x-data="batchSelect({{ $totalMatching ?? 0 }}, {{ session('batch_cleared', false) ? 'true' : 'false' }})"
             x-init="restorePersisted()">
            <div id="students-batch-bar">
                @include('students.partials.batch-bar')
            </div>
            <div id="students-table-mount">
                @include('students.partials.ajax-list')
            </div>
        </div>
    </x-page-card>

    @push('scripts')
    <script>
        document.addEventListener('htmx:afterSwap', function (e) {
            if (e.detail.target.id === 'students-table-mount') {
                var mount = document.getElementById('students-table-mount');
                var el = mount && mount.querySelector('[data-total-matching]');
                if (el) {
                    var newTotal = parseInt(el.dataset.totalMatching, 10);
                    if (!isNaN(newTotal)) {
                        var batchEl = mount.closest('[x-data^="batchSelect"]');
                        if (batchEl && window.Alpine) {
                            Alpine.$data(batchEl).totalMatching = newTotal;
                        }
                    }
                }
            }
        });
    </script>
    @endpush
    <x-archive-modal route="{{ url('archive/students/:id') }}" type="student" />
</x-app-layout>
