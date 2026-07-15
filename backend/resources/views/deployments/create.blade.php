<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Deployments'), 'url' => route('deployments.index')],
    ['label' => __('Add Deployment')],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Deployment Management') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Deployment') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Assign a student to a partner company for OJT.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('deployments.store') }}" method="POST" class="space-y-6"
                          x-data="{
                              studentData: @js($studentData),
                              companyData: @js($companyData),
                              selectedStudentId: {{ old('student_id') ? (int) old('student_id') : 'null' }},
                              selectedCompanyId: {{ old('company_id') ? (int) old('company_id') : 'null' }},
                              studentSearch: '',
                              companySearch: '',
                              studentSectionFilter: '',

                              get filteredStudents() {
                                  return this.studentData.filter(s => {
                                      if (this.studentSectionFilter && s.section !== this.studentSectionFilter) return false;
                                      if (this.studentSearch) {
                                          const q = this.studentSearch.toLowerCase();
                                          return s.student_number.toLowerCase().includes(q) || s.name.toLowerCase().includes(q);
                                      }
                                      return true;
                                  });
                              },

                              get filteredCompanies() {
                                  if (!this.companySearch) return this.companyData;
                                  const q = this.companySearch.toLowerCase();
                                  return this.companyData.filter(c => c.name.toLowerCase().includes(q));
                              },

                              get sections() {
                                  return [...new Set(this.studentData.map(s => s.section))].sort();
                              },

                              get selectedStudent() {
                                  return this.studentData.find(s => s.id === this.selectedStudentId) || null;
                              },

                              get selectedCompany() {
                                  return this.companyData.find(c => c.id === this.selectedCompanyId) || null;
                              },

                              selectStudent(id) {
                                  this.selectedStudentId = id;
                              },

                              selectCompany(id) {
                                  this.selectedCompanyId = id;
                              },

                              clearCompany() {
                                  this.selectedCompanyId = null;
                                  this.companySearch = '';
                              }
                          }">
                        @csrf

                        <input type="hidden" name="student_id" :value="selectedStudentId">
                        <input type="hidden" name="company_id" :value="selectedCompanyId">

                        {{-- Student Picker --}}
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <i class="bi bi-person text-sm"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Student') }}</h3>
                                        <p class="text-xs text-gray-500">{{ __('Only unassigned or unplaced students are shown') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4 space-y-3">
                                {{-- Selected chip --}}
                                <template x-if="selectedStudent">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg">
                                        <span class="text-sm font-medium text-emerald-800">
                                            <i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i>
                                            <span x-text="selectedStudent.student_number"></span> —
                                            <span x-text="selectedStudent.name"></span>
                                            <span class="text-emerald-600">(<span x-text="selectedStudent.section"></span>)</span>
                                        </span>
                                        <button type="button" @click="selectedStudentId = null; studentSearch = ''"
                                                class="ml-auto text-emerald-600 hover:text-emerald-800">
                                            <i class="bi bi-x-lg text-xs"></i>
                                        </button>
                                    </div>
                                </template>

                                {{-- Search --}}
                                <div class="relative">
                                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" x-model="studentSearch"
                                           placeholder="{{ __('Search by student number or name...') }}"
                                           class="block w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                </div>

                                {{-- Section filters --}}
                                <div class="flex flex-wrap gap-1.5">
                                    <button type="button" @click="studentSectionFilter = ''"
                                            :class="studentSectionFilter === '' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                            class="px-3 py-1 rounded-full text-xs font-medium transition-colors">
                                        {{ __('All') }}
                                    </button>
                                    <template x-for="sec in sections" :key="sec">
                                        <button type="button" @click="studentSectionFilter = sec"
                                                :class="studentSectionFilter === sec ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                                class="px-3 py-1 rounded-full text-xs font-medium transition-colors"
                                                x-text="sec">
                                        </button>
                                    </template>
                                </div>

                                {{-- Student list --}}
                                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                                    <template x-if="filteredStudents.length === 0">
                                        <div class="p-6 text-center text-sm text-gray-400">
                                            <i class="bi bi-inbox text-2xl block mb-1"></i>
                                            {{ __('No students match your search.') }}
                                        </div>
                                    </template>
                                    <template x-for="s in filteredStudents" :key="s.id">
                                        <button type="button" @click="selectStudent(s.id)"
                                                :class="selectedStudentId === s.id ? 'bg-emerald-50 border-l-2 border-emerald-600' : 'hover:bg-gray-50 border-l-2 border-transparent'"
                                                class="w-full text-left px-4 py-3 transition-colors flex items-center gap-3">
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center"
                                                 :class="selectedStudentId === s.id ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300'">
                                                <div x-show="selectedStudentId === s.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-medium text-gray-900 truncate" x-text="s.name"></div>
                                                <div class="text-xs text-gray-500">
                                                    <span x-text="s.student_number"></span>
                                                    <span class="mx-1">·</span>
                                                    <span x-text="s.section"></span>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <div class="text-xs text-gray-400" x-text="filteredStudents.length + ' of ' + studentData.length + ' students'"></div>

                                <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                            </div>
                        </div>

                        {{-- Company Picker --}}
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <i class="bi bi-building text-sm"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Company (optional)') }}</h3>
                                        <p class="text-xs text-gray-500">{{ __('Choose a partner company or assign later') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4 space-y-3">
                                {{-- Selected chip --}}
                                <template x-if="selectedCompany">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                                        <span class="text-sm font-medium text-blue-800">
                                            <i class="bi bi-check-circle-fill text-blue-500 mr-1"></i>
                                            <span x-text="selectedCompany.name"></span>
                                            <span class="text-blue-600" x-text="'(' + selectedCompany.industry + ')'"></span>
                                        </span>
                                        <button type="button" @click="clearCompany()"
                                                class="ml-auto text-blue-600 hover:text-blue-800">
                                            <i class="bi bi-x-lg text-xs"></i>
                                        </button>
                                    </div>
                                </template>

                                {{-- No company button --}}
                                <button type="button" @click="clearCompany()"
                                        :class="!selectedCompanyId ? 'bg-gray-100 border-gray-300 text-gray-700' : 'border-dashed border-gray-300 text-gray-500 hover:border-gray-400 hover:text-gray-700'"
                                        class="w-full rounded-lg border-2 px-4 py-2.5 text-sm font-medium transition-colors text-left">
                                    <i class="bi bi-dash-circle mr-1.5"></i>
                                    {{ __('No company (assign later)') }}
                                </button>

                                {{-- Search --}}
                                <div class="relative">
                                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input type="text" x-model="companySearch"
                                           placeholder="{{ __('Search company...') }}"
                                           class="block w-full rounded-lg border-gray-300 pl-9 pr-3 py-2 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                </div>

                                {{-- Company list --}}
                                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-100">
                                    <template x-if="filteredCompanies.length === 0">
                                        <div class="p-6 text-center text-sm text-gray-400">
                                            <i class="bi bi-inbox text-2xl block mb-1"></i>
                                            {{ __('No companies match your search.') }}
                                        </div>
                                    </template>
                                    <template x-for="c in filteredCompanies" :key="c.id">
                                        <button type="button" @click="selectCompany(c.id)"
                                                :class="selectedCompanyId === c.id ? 'bg-blue-50 border-l-2 border-blue-600' : 'hover:bg-gray-50 border-l-2 border-transparent'"
                                                class="w-full text-left px-4 py-3 transition-colors flex items-center gap-3">
                                            <div class="w-4 h-4 rounded-full border-2 flex-shrink-0 flex items-center justify-center"
                                                 :class="selectedCompanyId === c.id ? 'border-blue-600 bg-blue-600' : 'border-gray-300'">
                                                <div x-show="selectedCompanyId === c.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-medium text-gray-900 truncate" x-text="c.name"></div>
                                                <div class="text-xs text-gray-500" x-text="c.industry"></div>
                                            </div>
                                        </button>
                                    </template>
                                </div>

                                <div class="text-xs text-gray-400" x-text="filteredCompanies.length + ' of ' + companyData.length + ' companies'"></div>

                                <x-input-error :messages="$errors->get('company_id')" class="mt-1" />
                                <p class="text-xs text-gray-500">
                                    {{ __('Deployment will be created as pending. You can assign a company later from the deployment edit page.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('deployments.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest btn-primary">
                                {{ __('Create Deployment') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
