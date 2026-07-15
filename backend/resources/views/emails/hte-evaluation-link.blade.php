@extends('layouts.email')

@section('title', __('OJT Evaluation Request'))
@section('header', __('OJT Evaluation Request'))

@section('content')
    <p style="margin: 0 0 16px; font-size: 15px; color: #374151; line-height: 1.6;">{{ __('Hello,') }}</p>

    <p style="margin: 0 0 16px; font-size: 15px; color: #374151; line-height: 1.7;">
        {{ __("You have been requested to submit an OJT evaluation for the student below.") }}
    </p>

    <table cellpadding="0" cellspacing="0" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; margin: 0 0 24px; width: 100%;">
        <tr>
            <td style="padding: 16px 20px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size: 14px; color: #166534; font-weight: 600; padding-bottom: 4px;">{{ __('Student') }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 16px; color: #065f46; font-weight: 700;">{{ $studentName }}</td>
                    </tr>
                    @if (!empty($companyName))
                        <tr>
                            <td style="font-size: 14px; color: #166534; font-weight: 600; padding-top: 12px; padding-bottom: 4px;">{{ __('Company') }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 15px; color: #065f46;">{{ $companyName }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 8px; font-size: 15px; color: #374151;">
        {{ __('Click the button below to submit your evaluation:') }}
    </p>

    <table cellpadding="0" cellspacing="0" style="margin: 0 0 24px;">
        <tr>
            <td style="background: #059669; border-radius: 8px; text-align: center;">
                <a href="{{ $transactionUrl }}" style="display: inline-block; padding: 13px 28px; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 600; border-radius: 8px;">
                    {{ __('Submit Evaluation') }}
                </a>
            </td>
        </tr>
    </table>

    @if ($expiresAt)
        <table cellpadding="0" cellspacing="0" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; margin: 0 0 4px; width: 100%;">
            <tr>
                <td style="padding: 12px 16px; font-size: 13px; color: #92400e; line-height: 1.5;">
                    <strong>{{ __('Expires:') }}</strong>
                    {{ $expiresAt->format('M d, Y h:i A') }}
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 20px 0 0; font-size: 12px; color: #9ca3af;">
        {{ __('If the button above does not work, copy and paste this URL into your browser:') }}
        <br>
        <a href="{{ $transactionUrl }}" style="color: #059669; word-break: break-all;">{{ $transactionUrl }}</a>
    </p>
@endsection
