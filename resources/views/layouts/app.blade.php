<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
        @stack('styles')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                body { background: #fff !important; }
                aside, .md\\:hidden, header, #toast-container, .loading-overlay, .btn, .no-print { display: none !important; }
                .main-content { padding: 0 !important; margin: 0 !important; }
                .overflow-x-auto { overflow: visible !important; }
                .custom-table, .min-w-full { width: 100% !important; }
                th, td { padding: 6px 8px !important; font-size: 11px !important; }
                .layout-section-y { padding: 0 !important; }
                .max-w-7xl, .max-w-7xl, .max-w-7xl { max-width: 100% !important; }
                .shadow-sm, .shadow { box-shadow: none !important; }
                .border, .border-r, .border-b, .border-t, .border-l { border-color: #ddd !important; }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        @php
            $panelMode = request()->boolean('panel');
            $isStudentRoute = request()->is('student/*') || request()->routeIs('attendance.check-in');
        @endphp
        <div class="h-screen bg-gray-100 flex overflow-hidden">
            @if (! $panelMode)
                <aside class="hidden md:block w-64 bg-white border-r border-gray-200 h-full">
                    @include('layouts.navigation')
                </aside>
            @endif

            <div class="flex-1 flex flex-col h-full overflow-hidden">
                @if (! $panelMode)
                    <div class="md:hidden bg-white border-b border-gray-200">
                        @include('layouts.navigation')
                    </div>
                @endif

                @isset($header)
                    <header class="bg-white shadow flex-shrink-0">
                        <div class="py-3 px-4 sm:px-6 lg:px-8 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                {{ $header }}
                            </div>
                            @if (auth()->check() || session()->has('student_account_id'))
                                <div class="flex items-center gap-2 shrink-0 pt-0.5">
                                    @include('notifications.partials.bell')
                                </div>
                            @endif
                        </div>
                    </header>
                @endisset

                <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none" aria-live="polite" aria-atomic="true"></div>

                <main class="flex-1 overflow-y-auto">
                    <div class="px-4 sm:px-6 lg:px-8 main-content {{ $isStudentRoute ? 'pb-20 md:pb-0' : '' }}">
                        <x-alert-message />
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        <div id="loadingOverlay" class="loading-overlay" style="display:none">
            <div class="loading-spinner-box">
                <div class="loading-spinner"></div>
                <p>Processing...</p>
            </div>
        </div>

        @stack('scripts')
        <script>
            // Clear stale batch selections from previous sessions (full page load only)
            try {
                var prefix = 'batch_';
                for (var i = sessionStorage.length - 1; i >= 0; i--) {
                    var key = sessionStorage.key(i);
                    if (key && key.indexOf(prefix) === 0) {
                        sessionStorage.removeItem(key);
                    }
                }
            } catch(e) {}

            @if (session('status'))
                document.addEventListener('DOMContentLoaded', function () {
                    window.showToast('{{ addslashes(session('status')) }}', '{{ session('status_type', 'success') }}', 5000, '{{ addslashes(session('undo_key', '')) }}');
                });
            @endif

            // Global HTMX error handler
            document.addEventListener('htmx:responseError', function (e) {
                var msg = '{{ __('Something went wrong. Please try again.') }}';
                try {
                    var resp = JSON.parse(e.detail.xhr.responseText);
                    if (resp.message) msg = resp.message;
                } catch (ex) {}
                window.showToast(msg, 'error', 5000);
            });
            @if ($isStudentRoute && (request()->attributes->get('studentPortalLimited') ?? false))
                (function () {
                    var hasPolledFullAccess = false;
                    function pollPortalAccess() {
                        if (hasPolledFullAccess) return;
                        fetch('{{ route("student.portal-access-status") }}', { headers: { 'Accept': 'application/json' } })
                            .then(function (r) { return r.json(); })
                            .then(function (data) {
                                if (data.has_full_access) {
                                    hasPolledFullAccess = true;
                                    window.showToast('{{ __("All documents approved! Reloading your dashboard...") }}', 'success', 4000);
                                    setTimeout(function () { window.location.reload(); }, 1500);
                                }
                            })
                            .catch(function () {});
                    }
                    pollPortalAccess();
                    setInterval(pollPortalAccess, 30000);
                })();
            @endif
        </script>
    </body>
</html>
