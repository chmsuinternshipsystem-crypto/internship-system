@extends('layouts.error')
@section('title', __('Page Not Found'))
@section('content')
<div class="text-center max-w-md">
    <p class="text-6xl font-semibold text-emerald-600">404</p>
    <h1 class="mt-2 text-xl font-semibold text-gray-800">{{ __('Page Not Found') }}</h1>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('The page you are looking for does not exist or has been moved.') }}
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ __('Go to home') }}
    </a>
</div>
@endsection
