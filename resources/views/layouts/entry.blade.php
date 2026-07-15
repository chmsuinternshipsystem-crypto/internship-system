<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }} — {{ __('Login') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important}html{scrollbar-gutter:stable}</style>
    </head>
    <body class="login-portal font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col md:flex-row">
            <div @class([
                'hidden md:flex bg-emerald-700 text-white items-center justify-center px-8 py-10 lg:py-12 relative overflow-hidden',
                'md:w-[45%]' => $wide,
                'md:w-1/2' => ! $wide,
            ]) style="background: linear-gradient(135deg, #065f46 0%, #047857 40%, #059669 100%);">
                <div class="absolute inset-0 opacity-[0.04]" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px, 30px 30px;"></div>
                <div class="max-w-md space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/10 rounded-full p-2">
                            <x-application-logo class="h-10 w-auto" />
                        </div>
                        <div class="text-sm font-medium tracking-wide uppercase">
                            <div>CHMSU Digital Systems</div>
                            <div class="text-emerald-100">Internship Monitoring</div>
                        </div>
                    </div>

                    <div>
                        <h1 class="text-3xl lg:text-4xl font-semibold leading-tight">
                            Streamlining<br>
                            Internship<br>
                            Success.
                        </h1>
                        <p class="mt-4 text-sm text-emerald-100 leading-relaxed">
                            A deployment and compliance monitoring system designed for the BSIS department
                            of Carlos Hilado Memorial State University – Talisay.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-emerald-100">
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            <span>Secure staff access</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            <span>Document compliance at a glance</span>
                        </div>
                    </div>
                </div>
            </div>

            <div @class([
                'w-full flex flex-col justify-center px-4 py-8 md:py-10 lg:py-12 min-h-0',
                'md:w-[55%]' => $wide,
                'md:w-1/2' => ! $wide,
            ])>
                <div @class([
                    'mx-auto w-full bg-white shadow-md rounded-xl px-5 py-6 sm:px-6 sm:py-7 border border-gray-100',
                    'max-w-4xl' => $wide,
                    'max-w-md' => ! $wide,
                ])>
                    <div class="mb-5 text-center">
                        <a href="{{ url('/') }}" class="inline-flex flex-col items-center gap-1 no-underline text-gray-800 max-w-full">
                            <x-application-logo class="h-9 w-auto max-w-full md:h-10" />
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>
        </div>
        <div id="loadingOverlay" class="loading-overlay" style="display:none">
            <div class="loading-spinner-box">
                <div class="loading-spinner"></div>
                <p>Signing in...</p>
            </div>
        </div>

        @stack('login-scripts')
    </body>
</html>
