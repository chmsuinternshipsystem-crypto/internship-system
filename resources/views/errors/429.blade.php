@extends('layouts.error')
@section('title', __('Too Many Requests'))
@section('content')
<div class="text-center max-w-md">
    <p class="text-6xl font-semibold text-red-600">429</p>
    <h1 class="mt-2 text-xl font-semibold text-gray-800">{{ __('Too Many Requests') }}</h1>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('You have made too many requests. Please wait a moment and try again.') }}
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ __('Go to home') }}
    </a>
</div>
@endsection
