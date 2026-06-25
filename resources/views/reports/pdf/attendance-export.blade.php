<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('Attendance Export') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #1f2937; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .subtitle { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 5px 4px; font-size: 8px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        td { padding: 4px; border-bottom: 1px solid #e5e7eb; }
        .Normal { color: #047857; }
        .Pending\ Review { color: #d97706; }
        .Resolved { color: #6b7280; }
        .No\ Location { color: #dc2626; }
    </style>
</head>
<body>
    <h1>{{ __('Attendance Export') }}</h1>
    <p class="subtitle">
        {{ __('Generated on') }} {{ now()->format('F d, Y h:i A') }}
        @if (request('date_from'))
            | {{ __('From') }}: {{ request('date_from') }}
        @endif
        @if (request('date_to'))
            | {{ __('To') }}: {{ request('date_to') }}
        @endif
        @if (request('section'))
            | {{ __('Section') }}: {{ request('section') }}
        @endif
        | {{ __('Total') }}: {{ $records->count() }} {{ __('records') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Student No.') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Section') }}</th>
                <th>{{ __('Check In') }}</th>
                <th>{{ __('Time Out') }}</th>
                <th>{{ __('Total (min)') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $att)
                <tr>
                    <td>{{ $att->check_in_at?->format('M d, Y') }}</td>
                    <td>{{ $att->student?->student_number }}</td>
                    <td>{{ $att->student?->name }}</td>
                    <td>{{ $att->student?->section ?? '-' }}</td>
                    <td>{{ $att->check_in_at?->format('h:i A') }}</td>
                    <td>{{ $att->time_out_at?->format('h:i A') ?? '-' }}</td>
                    <td>{{ $att->total_minutes ?? '-' }}</td>
                    <td>
                        @php
                            $label = match(true) {
                                $att->location_unavailable => __('No Location'),
                                $att->review_required && $att->resolution_status !== 'resolved' => __('Pending Review'),
                                $att->review_required && $att->resolution_status === 'resolved' => __('Resolved'),
                                default => __('Normal'),
                            };
                        @endphp
                        {{ $label }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:16px;color:#6b7280;">
                        {{ __('No attendance records found.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
