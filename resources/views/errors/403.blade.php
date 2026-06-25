@extends('layouts.error')
@section('title', __('Access Denied'))
@section('content')
<div class="text-center max-w-md">
    <p class="text-6xl font-semibold text-amber-600">403</p>
    <h1 class="mt-2 text-xl font-semibold text-gray-800">{{ __('Access denied') }}</h1>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('You do not have permission to access this page.') }}
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-emerald-700">
        {{ __('Back to home') }}
    </a>
</div>
@endsection
