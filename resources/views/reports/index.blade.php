<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Reports') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reports Dashboard') }}</h2>
            <p class="text-sm text-gray-500">{{ __('View deployed students, missing documents, compliance summaries, and attendance records.') }}</p>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ activeTab: '{{ request('tab', 'deployed') }}', search: '', section: '', myStudents: false, showFilters: false }"
         x-init="loadTab(activeTab)"
         @filter-changed.window="loadTab(activeTab)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- KPI Bar --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="rounded-lg bg-white px-4 py-3 shadow-sm border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-600">
                            <i class="bi bi-people text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Total') }}</p>
                            <p class="text-lg font-bold text-gray-900 leading-tight">{{ $kpis['totalStudents'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white px-4 py-3 shadow-sm border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <i class="bi bi-send text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Deployed') }}</p>
                            <p class="text-lg font-bold text-emerald-700 leading-tight">{{ $kpis['deployed'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg bg-white px-4 py-3 shadow-sm border border-gray-200">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                            <i class="bi bi-check-circle text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Completed') }}</p>
                            <p class="text-lg font-bold text-blue-700 leading-tight">{{ $kpis['completed'] }}</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('compliance.index', ['risk' => 1]) }}" class="block rounded-lg bg-white px-4 py-3 shadow-sm border border-gray-200 hover:bg-red-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-red-50 text-red-600">
                            <i class="bi bi-exclamation-triangle text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('At-Risk') }}</p>
                            <p class="text-lg font-bold text-red-700 leading-tight">{{ $kpis['atRisk'] }}</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ __('View details') }} &rarr;</p>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Tab + Filter Bar --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
                    <nav class="flex gap-1">
                        <button @click="activeTab = 'deployed'; loadTab('deployed')"
                                :class="activeTab === 'deployed' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors">
                            <i class="bi bi-building me-1"></i>{{ __('Deployed') }}
                        </button>
                        <button @click="activeTab = 'missing'; loadTab('missing')"
                                :class="activeTab === 'missing' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors">
                            <i class="bi bi-file-earmark-x me-1"></i>{{ __('Missing') }}
                        </button>
                        <button @click="activeTab = 'compliance'; loadTab('compliance')"
                                :class="activeTab === 'compliance' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors">
                            <i class="bi bi-clipboard-check me-1"></i>{{ __('Compliance') }}
                        </button>
                        <button @click="activeTab = 'attendance'; loadTab('attendance')"
                                :class="activeTab === 'attendance' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                                class="px-3 py-1.5 text-sm rounded-md transition-colors">
                            <i class="bi bi-clock-history me-1"></i>{{ __('Attendance') }}
                        </button>
                    </nav>
                    <div class="flex items-center gap-1.5">
                        <a x-show="activeTab === 'deployed'"
                           :href="'{{ route('reports.deployed-per-company') }}?export=pdf&search=' + search + '&section=' + section + '&my_students=' + (myStudents ? '1' : '0')"
                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-file-earmark-pdf"></i>{{ __('PDF') }}
                        </a>
                        <a x-show="activeTab === 'missing'"
                           :href="'{{ route('reports.missing-documents') }}?export=pdf&search=' + search + '&section=' + section + '&my_students=' + (myStudents ? '1' : '0')"
                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-file-earmark-pdf"></i>{{ __('PDF') }}
                        </a>
                        <a x-show="activeTab === 'compliance'"
                           :href="'{{ route('reports.compliance-summary') }}?export=pdf&search=' + search + '&section=' + section + '&my_students=' + (myStudents ? '1' : '0')"
                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-file-earmark-pdf"></i>{{ __('PDF') }}
                        </a>
                        <a x-show="activeTab === 'attendance'"
                           :href="'{{ route('reports.attendance-export') }}?search=' + search + '&section=' + section + '&my_students=' + (myStudents ? '1' : '0')"
                           class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            <i class="bi bi-download"></i>{{ __('CSV') }}
                        </a>
                        @if (auth()->user()?->role === 'dean')
                            <a href="{{ route('reports.executive-summary') }}"
                               class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                                <i class="bi bi-file-earmark-text"></i>{{ __('Exec') }}
                            </a>
                        @endif
                        <span class="w-px h-5 bg-gray-200 mx-1"></span>
                        <button @click="showFilters = !showFilters"
                                class="inline-flex items-center gap-1 px-2 py-1.5 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                            <i class="bi bi-funnel" :class="showFilters ? 'text-emerald-600' : ''"></i>
                            <span x-text="showFilters ? '{{ __('Hide') }}' : '{{ __('Filter') }}'"></span>
                        </button>
                    </div>
                </div>

                {{-- Collapsible Filter Bar --}}
                <div x-show="showFilters" x-collapse.duration.200ms class="px-4 py-3 border-b border-gray-100">
                    <form class="flex flex-wrap items-center gap-3"
                          @submit.prevent="loadTab(activeTab)">
                        <div class="relative w-[260px] shrink-0">
                            <i class="bi bi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input type="search" x-model="search" placeholder="{{ __('Search by name or number...') }}"
                                   class="w-full rounded-md border border-gray-300 bg-white pl-8 pr-2.5 py-1.5 text-sm placeholder-gray-400 focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400/30"
                                   @input.debounce.300ms="loadTab(activeTab)">
                        </div>
                        <select x-model="section" @change="loadTab(activeTab)" class="rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-sm focus:border-emerald-400 focus:ring-1 focus:ring-emerald-400/30 w-[130px]">
                            <option value="">{{ __('All sections') }}</option>
                            @foreach (['A', 'B', 'C', 'D'] as $sec)
                                <option value="{{ $sec }}">{{ __('Section') }} {{ $sec }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-1.5 text-sm text-gray-600 whitespace-nowrap">
                            <input type="checkbox" x-model="myStudents"
                                   class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                                   @change="loadTab(activeTab)">
                            {{ __('My Students') }}
                        </label>
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700 transition-colors shadow-sm">
                            <i class="bi bi-search"></i>{{ __('Apply') }}
                        </button>
                    </form>
                </div>

                {{-- Tab Content --}}
                <div id="report-content" class="p-4">
                    <div class="text-center py-12 text-sm text-gray-400">
                        <i class="bi bi-arrow-up text-2xl text-gray-200 block mb-2"></i>
                        <p>{{ __('Select a tab above to view data.') }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function loadTab(tab) {
            if (!window.htmx) return;
            var params = new URLSearchParams();
            params.set('tab', tab);
            var search = document.querySelector('[x-model="search"]')?.value || '';
            var section = document.querySelector('[x-model="section"]')?.value || '';
            var myStudents = document.querySelector('[x-model="myStudents"]')?.checked || false;
            if (search) params.set('search', search);
            if (section) params.set('section', section);
            if (myStudents) params.set('my_students', '1');
            var url = '{{ route('reports.index') }}?' + params.toString();
            window.htmx.ajax('GET', url, { target: '#report-content', swap: 'innerHTML' });
            window.history.replaceState({}, '', url);
        }
    </script>
    @endpush
</x-app-layout>
