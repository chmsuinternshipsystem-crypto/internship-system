<div x-data="{ tab: '{{ $tab ?? 'all' }}' }"
     x-init="$watch('tab', t => { fetch('{{ route('companies.show', $company) }}?tab=' + t + '&page=1').then(r => r.text()).then(html => { let d = document.createElement('div'); d.innerHTML = html; let newContent = d.querySelector('#company-students-list'); if (newContent) document.getElementById('company-students-list').innerHTML = newContent.innerHTML; }); })">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-800">{{ __('Students') }}</h4>
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5 text-xs font-medium">
            <button @click="tab = 'all'"
                    :class="tab === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-md transition-colors">
                {{ __('All') }}
            </button>
            <button @click="tab = 'pending'"
                    :class="tab === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-md transition-colors">
                {{ __('Pending') }}
            </button>
            <button @click="tab = 'deployed'"
                    :class="tab === 'deployed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                    class="px-3 py-1.5 rounded-md transition-colors">
                {{ __('Deployed') }}
            </button>
        </div>
    </div>

    <div id="company-students-list">
        @if ($deployments->isNotEmpty())
            <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg text-sm">
                @foreach ($deployments as $dep)
                    <li class="flex items-center justify-between gap-2 px-3 py-2.5 hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $dep->student?->name ?? __('Unknown') }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $dep->student?->student_number ?? '' }}
                                @if ($dep->student?->section)
                                    &middot; {{ $dep->student->section }}
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold shrink-0
                            @if($dep->status === 'active') bg-emerald-100 text-emerald-700
                            @elseif($dep->status === 'completed') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ Str::headline($dep->status) }}
                        </span>
                        <a href="{{ route('deployments.show', ['deployment' => $dep, 'return' => 'company']) }}"
                           class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 shrink-0">
                            {{ __('View') }} &rarr;
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3">
                @include('partials.htmx-pagination', ['paged' => $deployments, 'hxTarget' => '#company-students-list'])
            </div>
        @else
            <p class="text-sm text-gray-500 py-4 text-center">{{ __('No students found for this company.') }}</p>
        @endif
    </div>
</div>
