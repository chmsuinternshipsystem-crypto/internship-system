<?php

namespace App\Services;

use App\Models\Evaluation;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class EvaluationExportService
{
    public function generate(Evaluation $evaluation): string
    {
        $evaluation->load(['student', 'company']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        $phpWord->getCompatibility()->setOoxmlVersion(15);
        $phpWord->getDocInfo()->setTitle('OJT Evaluation Report');
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
            'ON-THE-JOB TRAINING EVALUATION REPORT',
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 10);

        $studentName = $evaluation->student?->name ?? '—';
        $companyName = $evaluation->company?->name ?? '—';
        $supervisorName = $this->extractSupervisorName($evaluation);

        $infoFont = ['name' => 'Arial', 'size' => 11];
        $section->addText("Name of Trainee:  {$studentName}", $infoFont, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $section->addText("Company Name:  {$companyName}", $infoFont, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $section->addText("Supervisor:  {$supervisorName}", $infoFont, ['spaceAfter' => 0, 'spaceBefore' => 0]);
        $section->addText(
            "Date Evaluated:  " . ($evaluation->evaluated_at ? $evaluation->evaluated_at->format('F d, Y') : '—'),
            $infoFont,
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 10);

        $section->addText(
            'Rating Scale:  1=Did not meet  2=Met minimum  3=Met normal  4=Fully met  5=Exceeded',
            ['name' => 'Arial', 'size' => 9, 'italic' => true],
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 8);

        $criteriaAverages = $evaluation->getCriteriaAverages();

        $activeCategories = \App\Models\EvaluationCriterion::getActiveCriteria();
        foreach ($activeCategories as $catKey => $category) {
            $this->categoryTable($section, $catKey, $category, $evaluation, $criteriaAverages);
            $this->spacer($section, 6);
        }

        $overall = $criteriaAverages['overall'] ?? null;
        $section->addText(
            'OVERALL RATING:  ' . ($overall !== null ? number_format($overall, 2) . ' / 5' : '—'),
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        if ($overall !== null) {
            $percent = round($overall * 20);
            $section->addText(
                "Equivalent Score: {$percent} / 100",
                ['name' => 'Arial', 'size' => 11, 'color' => '666666'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
            );
        }

        $this->spacer($section, 10);

        $comment = $evaluation->cleanCommentsForDisplay();
        $section->addText(
            'Comments / Suggestions:',
            ['name' => 'Arial', 'size' => 11, 'bold' => true],
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            $comment ?? 'None provided.',
            ['name' => 'Arial', 'size' => 10],
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 14);

        $section->addText(
            '_________________________________________',
            ['name' => 'Arial', 'size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "Supervisor's Signature over Printed Name",
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            "Date:  _______________",
            ['name' => 'Arial', 'size' => 10],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );

        $this->spacer($section, 8);
        $this->footerImage($section);

        $tmp = tempnam(sys_get_temp_dir(), 'eval_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return $tmp;
    }

    private function categoryTable($section, string $catKey, array $category, Evaluation $evaluation, array $averages): void
    {
        $scores = $evaluation->criteria_scores ?? [];

        $section->addText(
            $category['label'],
            ['name' => 'Arial', 'size' => 11, 'bold' => true],
            ['spaceAfter' => 2, 'spaceBefore' => 0]
        );

        $colWidths = [5000, 600, 600, 600, 600, 600];
        $hF = ['name' => 'Arial', 'size' => 9, 'bold' => true];
        $dF = ['name' => 'Arial', 'size' => 9];
        $cu = ['spaceAfter' => 0, 'spaceBefore' => 0];

        $cellStyle = [
            'borderTopSize' => 2, 'borderTopColor' => '000000',
            'borderBottomSize' => 2, 'borderBottomColor' => '000000',
            'borderLeftSize' => 2, 'borderLeftColor' => '000000',
            'borderRightSize' => 2, 'borderRightColor' => '000000',
            'cellMargin' => 30,
        ];

        $table = $section->addTable([
            'borderSize' => 0,
            'width' => 8000,
            'cellMargin' => 0,
        ]);

        $table->addRow();
        $table->addCell($colWidths[0], $cellStyle)
            ->addText('Criteria', $hF, ['alignment' => Jc::CENTER, ...$cu]);
        foreach (['1', '2', '3', '4', '5'] as $v) {
            $table->addCell($colWidths[1], $cellStyle)
                ->addText($v, $hF, ['alignment' => Jc::CENTER, ...$cu]);
        }

        foreach ($category['items'] as $itemKey => $itemLabel) {
            $value = $scores[$catKey][$itemKey] ?? null;
            $table->addRow();
            $table->addCell($colWidths[0], $cellStyle)
                ->addText($itemLabel, $dF, $cu);
            for ($v = 1; $v <= 5; $v++) {
                $checked = (int) $value === $v ? '✓' : '';
                $table->addCell($colWidths[1], $cellStyle)
                    ->addText($checked, $dF, ['alignment' => Jc::CENTER, ...$cu]);
            }
        }

        $catAvg = $averages[$catKey] ?? null;
        $avgText = $catAvg && $catAvg['average'] !== null
            ? 'Total Rating: ' . number_format($catAvg['average'], 2)
            : 'Total Rating: —';

        $table->addRow();
        $totalCell = $table->addCell(
            $colWidths[0] + array_sum(array_slice($colWidths, 1)),
            array_merge($cellStyle, ['gridSpan' => 6])
        );
        $totalCell->addText(
            $avgText,
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::END, ...$cu]
        );
    }

    private function extractSupervisorName(Evaluation $evaluation): string
    {
        $comment = trim((string) ($evaluation->comments ?? ''));
        if (preg_match('/Supervisor:\s*(.+?)(?:\s+Supervisor Email:|\R|$)/i', $comment, $m)) {
            return trim((string) ($m[1] ?? ''));
        }
        return '—';
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
