@extends('layouts.email')

@section('title', $subjectText)
@section('header', $subjectText)

@section('content')
    <p style="margin: 0 0 16px; font-size: 15px; color: #374151; line-height: 1.6;">{{ __('Hi :name,', ['name' => $recipientName]) }}</p>
    <p style="margin: 0 0 20px; font-size: 15px; color: #374151; line-height: 1.7;">{{ $bodyText }}</p>

    @if ($actionUrl)
        <table cellpadding="0" cellspacing="0" style="margin: 0 0 24px;">
            <tr>
                <td style="background: #059669; border-radius: 8px; text-align: center;">
                    <a href="{{ $actionUrl }}" style="display: inline-block; padding: 13px 28px; font-size: 14px; color: #ffffff; text-decoration: none; font-weight: 600; border-radius: 8px;">
                        {{ $actionLabel ?? __('View Details') }}
                    </a>
                </td>
            </tr>
        </table>
    @endif

    <p style="margin: 24px 0 0; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 16px;">
        {{ __('This is an automated notification from the Internship Management System. Please do not reply to this email.') }}
    </p>
@endsection
