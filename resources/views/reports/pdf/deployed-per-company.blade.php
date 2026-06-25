<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Deployed Students per Company') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 16px; }
        .company { margin-bottom: 14px; page-break-inside: avoid; }
        .company-name { font-size: 11px; font-weight: bold; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #e5e7eb; padding: 4px 6px; text-align: left; }
        th { background: #f9fafb; font-size: 9px; text-transform: uppercase; }
        .muted { color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ __('Deployed Students per Company') }}</h1>
    <div class="meta">{{ config('app.name') }} · {{ now()->format('Y-m-d H:i') }}</div>

    @forelse ($companies as $row)
        <div class="company">
            <div class="company-name">{{ $row['company']->name }}
                <span class="muted">({{ $row['deployments']->count() }} {{ __('deployments') }})</span>
            </div>
            @if ($row['deployments']->isEmpty())
                <p class="muted">{{ __('No deployments for this company.') }}</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Student No.') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Start Date') }}</th>
                            <th>{{ __('End Date') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($row['deployments'] as $dep)
                            <tr>
                                <td>{{ $dep->student?->student_number }}</td>
                                <td>{{ $dep->student?->name }}</td>
                                <td>{{ $dep->start_date?->format('Y-m-d') }}</td>
                                <td>{{ $dep->end_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $dep->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <p class="muted">{{ __('No companies found') }}</p>
    @endforelse
</body>
</html>
