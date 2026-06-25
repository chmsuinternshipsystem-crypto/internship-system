@extends('layouts.error')
@section('title', __('Session Expired'))
@section('content')
<div class="text-center max-w-md">
    <p class="text-6xl font-semibold text-amber-600">419</p>
    <h1 class="mt-2 text-xl font-semibold text-gray-800">{{ __('Session Expired') }}</h1>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('Your session has expired. Please sign in again to continue.') }}
    </p>
    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ __('Back to Login') }}
    </a>
</div>
@endsection
