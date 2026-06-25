<?php

namespace App\Services;

use App\Models\WeeklyJournal;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class WeeklyJournalExportService
{
    public function generate(WeeklyJournal $journal): string
    {
        $journal->load(['student', 'deployment.company']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        $phpWord->getCompatibility()->setOoxmlVersion(15);
        $phpWord->getDocInfo()->setTitle('Weekly Journal - Week ' . $journal->week_number);
        $phpWord->getDocInfo()->setCreator('CHMSU Internship System');

        $section = $phpWord->addSection([
            'paperSize' => 'Letter',
            'marginLeft' => Converter::inchToTwip(0.8),
            'marginRight' => Converter::inchToTwip(0.8),
            'marginTop' => Converter::inchToTwip(1),
            'marginBottom' => Converter::inchToTwip(1),
        ]);

        $this->headerImage($section);
        $this->spacer($section, 12);

        $section->addText(
            'ON-THE-JOB TRAINING',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            'WEEKLY JOURNAL',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 8);

        $student = $journal->student;
        $company = $journal->deployment?->company;
        $supervisorName = $journal->supervisor_name
            ?? $company?->contact_first_name . ' ' . $company?->contact_last_name
            ?? '';

        $infoFont = ['name' => 'Arial', 'size' => 11];

        $section->addText(
            "NAME: _________________________________  {$student->name}",
            $infoFont,
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "Department: Bachelor of Science in Information Systems",
            $infoFont,
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "HTE Name: _________________________________  " . ($company?->name ?? ''),
            $infoFont,
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "Inclusive Dates: _________________________________  "
                . $journal->week_start_date->format('F d, Y')
                . ' – ' . $journal->week_end_date->format('F d, Y'),
            $infoFont,
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 10);

        $this->activityTable($section, $journal);

        $this->spacer($section, 10);

        $section->addText(
            '________________________________________',
            ['name' => 'Arial', 'size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "SUPERVISOR'S NAME",
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        if ($supervisorName) {
            $section->addText(
                $supervisorName,
                ['name' => 'Arial', 'size' => 9, 'color' => '666666'],
                ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );
        }

        $this->spacer($section, 8);
        $this->footerImage($section);

        $tmp = tempnam(sys_get_temp_dir(), 'wj_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return $tmp;
    }

    private function activityTable($section, WeeklyJournal $journal): void
    {
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $activities = $journal->activities ?? [];
        $files = $journal->files ?? [];

        $colWidths = [1200, 5400, 3400];

        $hF = ['name' => 'Arial', 'size' => 10, 'bold' => true];
        $dF = ['name' => 'Arial', 'size' => 10];
        $tF = ['name' => 'Arial', 'size' => 9];
        $cu = ['spaceAfter' => 0, 'spaceBefore' => 0];

        $cellStyle = [
            'borderTopSize' => 2, 'borderTopColor' => '000000',
            'borderBottomSize' => 2, 'borderBottomColor' => '000000',
            'borderLeftSize' => 2, 'borderLeftColor' => '000000',
            'borderRightSize' => 2, 'borderRightColor' => '000000',
            'cellMargin' => 40,
        ];

        $table = $section->addTable([
            'borderSize' => 0,
            'width' => 10000,
            'cellMargin' => 0,
        ]);

        $table->addRow();
        $table->addCell($colWidths[0], $cellStyle)
            ->addText('DAY', $hF, ['alignment' => Jc::CENTER, ...$cu]);
        $table->addCell($colWidths[1], $cellStyle)
            ->addText("STUDENT'S ACTIVITIES/TASKS/SEMINARS ATTENDED", $hF, ['alignment' => Jc::CENTER, ...$cu]);
        $table->addCell($colWidths[2], $cellStyle)
            ->addText('Supporting Documents', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        foreach ($dayNames as $day) {
            $dayKey = strtolower($day);
            $dayActivities = $activities[$dayKey] ?? '';
            $dayFiles = $files[$dayKey] ?? [];

            $fileText = '';
            if (is_array($dayFiles)) {
                $names = [];
                foreach ($dayFiles as $f) {
                    $name = $f['name'] ?? $f['file_name'] ?? (is_string($f) ? basename($f) : '');
                    if ($name) {
                        $names[] = $name;
                    }
                }
                $fileText = implode("\n", $names);
            }

            $table->addRow();
            $table->addCell($colWidths[0], $cellStyle)
                ->addText($day, $dF, ['alignment' => Jc::CENTER, ...$cu]);
            $table->addCell($colWidths[1], $cellStyle)
                ->addText($dayActivities, $tF, $cu);
            $table->addCell($colWidths[2], $cellStyle)
                ->addText($fileText, $tF, $cu);
        }
    }

    private function headerImage($section): void
    {
        $path = public_path('images/CHMSU Header.png');
        if (file_exists($path)) { try { $section->addImage($path, ['width' => 540, 'alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]); } catch (\Throwable $e) { report($e); } }
    }

    private function footerImage($section): void
    {
        $path = public_path('images/CHMSU FOOTER.png');
        if (file_exists($path)) { try { $section->addImage($path, ['width' => 360, 'alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]); } catch (\Throwable $e) { report($e); } }
    }

    private function spacer($section, int $pts = 8): void
    {
        $section->addText('', ['name' => 'Arial', 'size' => $pts], ['spaceAfter' => 0, 'spaceBefore' => 0]);
    }
}
