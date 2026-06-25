<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Compliance Summary') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111827; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 10px; }
        .stats { margin-bottom: 12px; border: 1px solid #e5e7eb; padding: 8px; }
        .stats span { margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 4px 5px; text-align: left; }
        th { background: #f9fafb; font-size: 8px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>{{ __('Compliance Summary') }}</h1>
    <div class="meta">{{ config('app.name') }} · {{ now()->format('Y-m-d H:i') }}</div>
    <div class="stats">
        <span><strong>{{ __('Total') }}:</strong> {{ $summary['total'] }}</span>
        <span><strong>{{ __('Complete') }}:</strong> {{ $summary['compliant'] }}</span>
        <span><strong>{{ __('In Progress') }}:</strong> {{ $summary['partially_compliant'] }}</span>
        <span><strong>{{ __('Needs Attention') }}:</strong> {{ $summary['non_compliant'] }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Student No.') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Program') }}</th>
                <th>{{ __('Section') }}</th>
                <th>{{ __('Submitted') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['student']->student_number }}</td>
                    <td>{{ $row['student']->name }}</td>
                    <td>{{ $row['student']->program }}</td>
                    <td>{{ $row['student']->section }}</td>
                    <td>{{ $row['submitted'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ match($row['status']) { 'compliant' => __('Complete'), 'partially_compliant' => __('In Progress'), 'non_compliant' => __('Needs Attention'), default => str_replace('_', ' ', ucfirst((string) $row['status'])) } }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
