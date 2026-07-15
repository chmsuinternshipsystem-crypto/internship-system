<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Certificate of Completion</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Serif', 'Georgia', 'Times New Roman', serif;
            color: #374151;
        }
        .cert-border {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            border: 4pt solid #059669;
            pointer-events: none;
        }
        .cert-content {
            padding: 14mm 18mm 10mm;
        }
        .hdr-tbl {
            margin: 0 auto;
            border-collapse: collapse;
        }
        .hdr-tbl td {
            vertical-align: middle;
            padding: 0;
        }
        .hdr-logo {
            width: 26mm;
            height: auto;
            display: block;
            margin-right: 4mm;
        }
        .hdr-uni {
            font-size: 14pt;
            font-weight: 700;
            color: #065f46;
        }
        .hdr-college {
            font-size: 8.5pt;
            color: #4b5563;
        }
        .divider {
            width: 45mm;
            height: 1pt;
            background: #059669;
            margin: 2mm auto;
        }
        .cert-title {
            font-size: 20pt;
            font-weight: 700;
            color: #065f46;
            text-align: center;
            letter-spacing: 1pt;
            margin: 1mm 0;
        }
        .label {
            text-align: center;
            font-size: 10pt;
            color: #4b5563;
            margin: 0.5mm 0;
        }
        .name {
            text-align: center;
            font-size: 16pt;
            font-weight: 700;
            font-style: italic;
            color: #065f46;
            margin: 0.8mm 0;
        }
        .cname {
            text-align: center;
            font-size: 12pt;
            font-weight: 700;
            color: #065f46;
            margin: 0.8mm 0;
        }
        .date {
            text-align: center;
            font-size: 8.5pt;
            color: #6b7280;
            margin: 1mm 0;
        }
        .sig-tbl {
            width: 75%;
            margin: 2mm auto 0;
            border-collapse: collapse;
        }
        .sig-tbl td {
            width: 50%;
            text-align: center;
            padding: 0 3mm;
        }
        .sig-line {
            width: 65%;
            margin: 0 auto 0.5mm;
            border-top: 1pt solid #374151;
        }
        .sig-name {
            font-size: 9pt;
            font-weight: 700;
            color: #374151;
            margin: 0;
        }
        .sig-role {
            font-size: 8pt;
            color: #6b7280;
            margin: 0;
        }
        .ref {
            font-size: 6.5pt;
            color: #9ca3af;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    <div class="cert-border"></div>
    <div class="cert-content">
        <table class="hdr-tbl">
            <tr>
                <td style="text-align:center;">
                    @php $p = public_path('images/logo.png'); if (!file_exists($p)) $p = public_path('images/CHMSU Header.png'); @endphp
                    @if (file_exists($p)) <img src="{{ $p }}" class="hdr-logo" alt=""> @endif
                </td>
                <td style="text-align:left;">
                    <div class="hdr-uni">CARLOS HILADO MEMORIAL STATE UNIVERSITY</div>
                    @if (!empty($collegeName)) <div class="hdr-college">{{ $collegeName }}</div> @endif
                </td>
            </tr>
        </table>
        <div class="divider"></div>
        <div class="cert-title">Certificate of Completion</div>
        <div class="label">This is to certify that</div>
        <div class="name">{{ $studentName }}</div>
        <div class="label">has completed the required <strong>{{ $totalHours }} hours</strong> of On-the-Job Training at</div>
        <div class="cname">{{ $companyName }}</div>
        <div class="label" style="font-size:9pt;">Bachelor of Science in Information Systems</div>
        <div class="date">Given this {{ $issuedDay }} day of {{ $issuedMonth }}, {{ $issuedYear }}.</div>
        <table class="sig-tbl">
            <tr>
                <td><div class="sig-line"></div><p class="sig-name">{{ $coordinatorName }}</p><p class="sig-role">OJT Coordinator</p></td>
                <td><div class="sig-line"></div><p class="sig-name">{{ $chairpersonName }}</p><p class="sig-role">Program Chairperson</p></td>
            </tr>
        </table>
        <div class="ref">Ref. No: {{ $referenceNumber }}</div>
    </div>
</body>
</html>
