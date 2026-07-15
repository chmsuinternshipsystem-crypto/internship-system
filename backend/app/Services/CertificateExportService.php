<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateExportService
{
    public function generate(Student $student, Certificate $certificate, string $coordinatorName = 'OJT Coordinator', string $chairpersonName = 'Program Chairperson'): string
    {
        $deployment = $certificate->deployment ?: $student->deployments()->where('status', 'completed')->latest()->first();
        $companyName = $deployment?->company?->name ?? 'Partner Company';
        $totalHours = (int) $student->getTotalAttendanceMinutes();
        $hoursFormatted = $totalHours >= 36000 ? '600' : (string) round($totalHours / 60);

        $now = now();
        $issuedDay = $now->format('jS');
        $issuedMonth = $now->format('F');
        $issuedYear = $now->format('Y');

        if (! $certificate->reference_number) {
            $certificate->reference_number = 'CERT-' . $issuedYear . '-' . str_pad((string) $certificate->id, 4, '0', STR_PAD_LEFT);
            $certificate->save();
        }

        $data = [
            'studentName' => $student->name,
            'companyName' => $companyName,
            'totalHours' => $hoursFormatted,
            'issuedDay' => $issuedDay,
            'issuedMonth' => $issuedMonth,
            'issuedYear' => $issuedYear,
            'referenceNumber' => $certificate->reference_number,
            'coordinatorName' => $coordinatorName,
            'chairpersonName' => $chairpersonName,
            'collegeName' => 'College of Computer Studies',
        ];

        $pdf = Pdf::loadView('pdf.certificate', $data);
        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('defaultFont', 'DejaVu Serif');
        $pdf->setOption('isRemoteEnabled', true);

        $filename = 'certificate-' . Str::slug($student->name) . '-' . $certificate->id . '.pdf';
        $path = 'certificates/generated/' . $filename;

        if (! is_dir(storage_path('app/public/certificates/generated'))) {
            mkdir(storage_path('app/public/certificates/generated'), 0755, true);
        }

        $pdf->save(storage_path('app/public/' . $path));

        return $path;
    }
}
