<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }} — {{ __('OJT Evaluation') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}html{scrollbar-gutter:stable}</style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col">
            {{-- Top bar --}}
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-100 rounded-full p-1.5">
                            <img src="{{ asset('images/logo.png') }}" alt="CHMSU" class="h-7 w-auto">
                        </div>
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-emerald-700 uppercase">CHMSU-Talisay</p>
                            <p class="text-[11px] text-gray-500">{{ __('BSIS Internship Program') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-semibold text-gray-700">{{ __('OJT Evaluation') }}</p>
                        <p class="text-[10px] text-gray-400">{{ __('Online Assessment Form') }}</p>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 py-6">
                <div class="max-w-5xl mx-auto px-4">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-100 bg-white py-3">
                <div class="max-w-5xl mx-auto px-4 text-center">
                    <p class="text-[11px] text-gray-400">
                        {{ __('Carlos Hilado Memorial State University — Talisay') }} ·
                        {{ __('BSIS Internship Monitoring System') }}
                    </p>
                </div>
            </footer>
        </div>

        {{-- Loading overlay --}}
        <div id="loadingOverlay" class="loading-overlay" style="display:none">
            <div class="loading-spinner-box">
                <div class="loading-spinner"></div>
                <p>{{ __('Submitting...') }}</p>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
