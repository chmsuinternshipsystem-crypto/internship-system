@extends('layouts.email')

@section('title', __('Student Portal Login Code'))
@section('header', __('Student Portal Login Code'))

@section('content')
    <p style="margin: 0 0 16px; font-size: 15px; color: #374151; line-height: 1.6;">{{ __('Hi :name,', ['name' => $studentName]) }}</p>

    <p style="margin: 0 0 20px; font-size: 15px; color: #374151; line-height: 1.7;">
        {{ __('Use the code below to finish signing in to your student portal. This code expires in a few minutes.') }}
    </p>

    <table cellpadding="0" cellspacing="0" style="margin: 0 auto 24px; background: #f0fdf4; border: 2px dashed #059669; border-radius: 10px; width: 100%;">
        <tr>
            <td align="center" style="padding: 24px 16px;">
                <span style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #065f46; font-family: 'Courier New', Courier, monospace;">{{ $otpCode }}</span>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; margin: 0 0 4px; width: 100%;">
        <tr>
            <td style="padding: 12px 16px; font-size: 13px; color: #991b1b; line-height: 1.5;">
                {{ __('If you did not request this code, you can safely ignore this email.') }}
            </td>
        </tr>
    </table>
@endsection
