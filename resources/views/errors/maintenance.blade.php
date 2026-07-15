@extends('layouts.error')
@section('title', __('Under Maintenance'))
@section('content')
<div class="text-center max-w-md">
    <div class="text-6xl text-emerald-600">
        <svg class="mx-auto h-16 w-16 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-7.083-7.083a2 2 0 010-2.828l1.414-1.414a2 2 0 012.828 0l7.083 7.083m-7.083 7.083l7.083-7.083m-7.083 7.083a2 2 0 01-2.828 0l-1.414-1.414a2 2 0 010-2.828l7.083-7.083m0 0l7.083 7.083m-7.083-7.083L18.364 5.636a2 2 0 012.828 0l1.414 1.414a2 2 0 010 2.828l-7.083 7.083" />
        </svg>
    </div>
    <h1 class="mt-4 text-xl font-semibold text-gray-800">{{ __('System Under Maintenance') }}</h1>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('We are currently performing scheduled maintenance. Please check back shortly.') }}
    </p>
    <p class="mt-1 text-xs text-gray-400">
        {{ __('Only authorized personnel can access the system at this time.') }}
    </p>
    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ __('Back to Login') }}
    </a>
</div>
@endsection
