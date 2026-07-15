<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Missing / Pending Documents') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 14px; margin: 0 0 8px 0; }
        .meta { font-size: 9px; color: #6b7280; margin-bottom: 14px; }
        .card { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; margin-bottom: 12px; page-break-inside: avoid; }
        .student-name { font-weight: bold; font-size: 11px; }
        .student-meta { font-size: 9px; color: #4b5563; margin-top: 2px; }
        .cols { margin-top: 8px; width: 100%; }
        .col { vertical-align: top; width: 50%; padding-right: 8px; }
        .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .missing-title { color: #991b1b; }
        .pending-title { color: #1d4ed8; }
        ul { margin: 0; padding-left: 14px; }
        li { margin-bottom: 2px; }
        .empty { color: #9ca3af; font-style: italic; }
    </style>
</head>
<body>
    <h1>{{ __('Missing / Pending Documents') }}</h1>
    <div class="meta">{{ config('app.name') }} · {{ now()->format('Y-m-d H:i') }}</div>

    @forelse ($students as $row)
        <div class="card">
            <div class="student-name">{{ $row['student']->name }}</div>
            <div class="student-meta">
                {{ $row['student']->student_number }} · {{ $row['student']->program }} / {{ $row['student']->section }}
            </div>
            <table class="cols">
                <tr>
                    <td class="col">
                        <div class="section-title missing-title">{{ __('Missing') }}</div>
                        @if (count($row['missing']))
                            <ul>
                                @foreach ($row['missing'] as $name)
                                    <li>{{ $name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="empty">{{ __('None') }}</span>
                        @endif
                    </td>
                    <td class="col">
                        <div class="section-title pending-title">{{ __('Pending') }}</div>
                        @if (count($row['pending']))
                            <ul>
                                @foreach ($row['pending'] as $name)
                                    <li>{{ $name }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span class="empty">{{ __('None') }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @empty
        <p class="meta">{{ __('No students have missing or pending mandatory documents.') }}</p>
    @endforelse
</body>
</html>
