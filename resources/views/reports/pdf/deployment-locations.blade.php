<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Deployment Locations') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 16px; }
        .company-section { margin-bottom: 20px; }
        .company-section h3 { font-size: 12px; margin: 0 0 2px; color: #047857; }
        .company-section .address { font-size: 9px; color: #6b7280; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 6px 4px; font-size: 9px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        td { padding: 4px; border-bottom: 1px solid #e5e7eb; }
        .active { color: #047857; }
        .completed { color: #6b7280; }
        .withdrawn { color: #dc2626; }
    </style>
</head>
<body>
    <h1>{{ __('Deployment Locations') }}</h1>
    <p class="subtitle">{{ __('Generated on') }} {{ now()->format('F d, Y h:i A') }}</p>

    @foreach ($companies as $row)
        <div class="company-section">
            <h3>{{ $row['company']->name }} ({{ $row['deployments']->count() }})</h3>
            @if ($row['company']->address)
                <p class="address">{{ $row['company']->address }}</p>
            @endif
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Student No.') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Section') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($row['deployments'] as $dep)
                        <tr>
                            <td>{{ $dep->student?->student_number }}</td>
                            <td>{{ $dep->student?->name }}</td>
                            <td>{{ $dep->student?->section ?? '-' }}</td>
                            <td>{{ $dep->start_date?->format('M d, Y') }}</td>
                            <td>{{ $dep->end_date?->format('M d, Y') ?? '-' }}</td>
                            <td class="{{ $dep->status }}">{{ Str::headline($dep->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
