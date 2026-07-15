<?php

namespace App\Services;

use App\Models\DailyTimeRecord;
use App\Models\Student;
use Carbon\Carbon;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class DttrExportService
{
    private const COL = [613, 744, 749, 748, 753, 880, 6486];
    private const PAGE_W = 10972;
    private const RPT = "\u{2014}";

    public function generate(Student $student, string $companyName, int $year, int $month): string
    {
        $records = DailyTimeRecord::where('student_id', $student->id)
            ->whereBetween('date', [
                Carbon::create($year, $month, 1)->startOfMonth(),
                Carbon::create($year, $month, 1)->endOfMonth(),
            ])
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($r) => (int) $r->date->format('j'));

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'paperSize' => 'Letter',
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.27),
            'marginTop' => Converter::inchToTwip(1),
            'marginBottom' => Converter::inchToTwip(1),
        ]);

        $this->headerImage($section);
        $this->title($section);
        $this->spacer($section);
        $this->nameLine($section);
        $this->spacer($section, center: true);
        $this->spacer($section, center: true);
        $this->companyLine($section);
        $this->labelCenter($section, 'INDUSTRY / AGENCY NAME / DEPARTMENT', 10);
        $this->spacer($section, center: true);
        $this->monthLine($section);
        $this->labelCenter($section, 'MONTH', 10);
        $this->spacer($section);
        $this->dateLine($section);
        $this->officialHour($section);
        $this->spacer($section, pts: 28);
        $this->spacer($section, pts: 28);
        $this->dttrTable($section, $records);
        $this->spacer($section);
        $this->spacer($section);
        $this->totalHours($section, $records);
        $this->certification($section);
        $this->spacer($section);
        $this->signatures($section, $student->name);
        $this->footerImage($section);

        $tmp = tempnam(sys_get_temp_dir(), 'dttr_') . '.docx';
        IOFactory::createWriter($phpWord, 'Word2007')->save($tmp);

        return $tmp;
    }

    private function headerImage($section): void
    {
        $this->addImageBody($section, 'images/CHMSU Header.png', 540);
    }

    private function footerImage($section): void
    {
        $this->addImageBody($section, 'images/CHMSU FOOTER.png', 360);
    }

    private function addImageBody($section, string $relPath, int $width): void
    {
        $path = public_path($relPath);
        if (!file_exists($path)) {
            return;
        }
        try {
            $info = getimagesize($path);
            if ($info === false) {
                return;
            }
            [$w, $h] = $info;
            $r = $width / $w;
            $section->addImage($path, [
                'width' => $width,
                'height' => (int) round($h * $r),
                'alignment' => Jc::CENTER,
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function title($section): void
    {
        $section->addText(
            'DAILY TIME & TASKS RECORD (DTTR)',
            ['name' => 'Arial', 'size' => 12, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function spacer($section, bool $center = false, int $pts = 8): void
    {
        $style = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1];
        if ($center) {
            $style['alignment'] = Jc::CENTER;
        }
        $section->addText('', ['name' => 'Arial', 'size' => $pts / 2], $style);
    }

    private function nameLine($section): void
    {
        $section->addText(
            '_________________________________________________________  NAME',
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function companyLine($section): void
    {
        $section->addText(
            '_________________________________________________________',
            ['name' => 'Arial', 'size' => 11],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function labelCenter($section, string $text, int $size): void
    {
        $section->addText(
            $text,
            ['name' => 'Arial', 'size' => $size, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function monthLine($section): void
    {
        $section->addText(
            '___________________________',
            ['name' => 'Arial', 'size' => 11],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function dateLine($section): void
    {
        $text = implode('   ' . self::RPT . '   ', [
            str_repeat('_', 11),
            str_repeat('_', 11),
            str_repeat('_', 11),
            str_repeat('_', 11),
        ]);
        $section->addText(
            $text,
            ['name' => 'Arial', 'size' => 11],
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function officialHour($section): void
    {
        $section->addText(
            "\t\t\t\t\tOfficial Hour Of Arrival And Departure",
            ['name' => 'Arial', 'size' => 14, 'bold' => true],
            ['spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function dttrTable($section, $records): void
    {
        $thk = 8;
        $thn = 2;

        $cell = function (array $extra = []) use ($thk, $thn) {
            return array_merge([
                'borderTopSize' => $thn, 'borderTopColor' => '000000',
                'borderBottomSize' => $thn, 'borderBottomColor' => '000000',
                'borderLeftSize' => $thn, 'borderLeftColor' => '000000',
                'borderRightSize' => $thn, 'borderRightColor' => '000000',
                'cellMargin' => 0,
            ], $extra);
        };

        $table = $section->addTable([
            'borderSize' => 0,
            'width' => self::PAGE_W,
            'cellMargin' => 0,
        ]);

        $c = self::COL;
        $hF = ['name' => 'Arial', 'size' => 11, 'bold' => true];
        $sF = ['name' => 'Arial', 'size' => 5];
        $dF = ['name' => 'Arial', 'size' => 11];
        $dayF = ['name' => 'Arial', 'size' => 8, 'bold' => true];
        $tF = ['name' => 'Arial', 'size' => 7.5];
        $cu = ['spaceAfter' => 0, 'spaceBefore' => 0];

        // Row 0: Main header
        $table->addRow();
        $table->addCell($c[0], $cell([
            'vMerge' => 'restart',
            'borderTopSize' => $thk, 'borderLeftSize' => $thk,
            'borderBottomSize' => $thn,
        ]))->addText('DAY', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[1] + $c[2], $cell([
            'gridSpan' => 2,
            'borderTopSize' => $thk, 'borderLeftSize' => $thk,
            'borderBottomSize' => $thk,
        ]))->addText('AM', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[3] + $c[4], $cell([
            'gridSpan' => 2,
            'borderTopSize' => $thk, 'borderLeftSize' => $thk,
            'borderBottomSize' => $thk,
        ]))->addText('PM', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        $hc = $table->addCell($c[5], $cell([
            'vMerge' => 'restart',
            'borderTopSize' => $thk, 'borderLeftSize' => $thk,
            'borderBottomSize' => $thn,
        ]));
        $hc->addText('NO. OF', $hF, ['alignment' => Jc::CENTER, ...$cu]);
        $hc->addText('HOURS', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[6], $cell([
            'vMerge' => 'restart',
            'borderTopSize' => $thk, 'borderLeftSize' => $thk,
            'borderBottomSize' => $thn,
            'borderRightSize' => $thk,
        ]))->addText('TASKS/ ASSIGNMENTS PERFORMED', $hF, ['alignment' => Jc::CENTER, ...$cu]);

        // Row 1: Sub-header
        $table->addRow();
        $table->addCell($c[0], $cell([
            'vMerge' => 'continue',
            'borderLeftSize' => $thk,
            'borderTopSize' => 0, 'borderBottomSize' => $thn,
        ]));

        $table->addCell($c[1], $cell([
            'borderTopSize' => $thk, 'borderLeftSize' => $thn,
            'borderBottomSize' => $thn,
        ]))->addText('ARRIVAL', $sF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[2], $cell([
            'borderTopSize' => $thk, 'borderLeftSize' => $thn,
            'borderBottomSize' => $thn,
        ]))->addText('DEPARTURE', $sF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[3], $cell([
            'borderTopSize' => $thk, 'borderLeftSize' => $thn,
            'borderBottomSize' => $thn,
        ]))->addText('ARRIVAL', $sF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[4], $cell([
            'borderTopSize' => $thk, 'borderLeftSize' => $thn,
            'borderBottomSize' => $thn,
        ]))->addText('DEPARTURE', $sF, ['alignment' => Jc::CENTER, ...$cu]);

        $table->addCell($c[5], $cell([
            'vMerge' => 'continue',
            'borderTopSize' => 0, 'borderBottomSize' => $thn,
        ]));

        $table->addCell($c[6], $cell([
            'vMerge' => 'continue',
            'borderTopSize' => 0, 'borderBottomSize' => $thn,
            'borderRightSize' => $thk,
        ]));

        // Rows 2-32: Data
        for ($day = 1; $day <= 31; $day++) {
            $r = $records->get($day);
            $table->addRow();

            $left = $day === 1 ? $thk : $thn;
            $right = $thn;

            if ($r) {
                $amIn = $r->am_arrival
                    ? Carbon::parse($r->am_arrival)->format('h:i A')
                    : ($r->time_in ? Carbon::parse($r->time_in)->format('h:i A') : '');
                $amOut = $r->am_departure
                    ? Carbon::parse($r->am_departure)->format('h:i A') : '';
                $pmIn = $r->pm_arrival
                    ? Carbon::parse($r->pm_arrival)->format('h:i A') : '';
                $pmOut = $r->pm_departure
                    ? Carbon::parse($r->pm_departure)->format('h:i A')
                    : ($r->time_out ? Carbon::parse($r->time_out)->format('h:i A') : '');
                $mins = $r->total_minutes ?? 0;
                $hr = intdiv($mins, 60);
                $mn = $mins % 60;
                $hrs = $hr > 0 || $mn > 0 ? sprintf('%dh %02dm', $hr, $mn) : '';
                $taskText = $r->tasks ?? '';

                $table->addCell($c[0], $cell(['borderLeftSize' => $left]))
                    ->addText((string) $day, $dayF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[1], $cell())
                    ->addText($amIn, $dF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[2], $cell())
                    ->addText($amOut, $dF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[3], $cell())
                    ->addText($pmIn, $dF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[4], $cell())
                    ->addText($pmOut, $dF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[5], $cell())
                    ->addText($hrs, $dF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[6], $cell(['borderRightSize' => $right]))
                    ->addText($taskText, $tF, ['alignment' => Jc::BOTH, ...$cu]);
            } else {
                $table->addCell($c[0], $cell(['borderLeftSize' => $left]))
                    ->addText((string) $day, $dayF, ['alignment' => Jc::CENTER, ...$cu]);
                $table->addCell($c[1], $cell())->addText('', $dF, $cu);
                $table->addCell($c[2], $cell())->addText('', $dF, $cu);
                $table->addCell($c[3], $cell())->addText('', $dF, $cu);
                $table->addCell($c[4], $cell())->addText('', $dF, $cu);
                $table->addCell($c[5], $cell())->addText('', $dF, $cu);
                $table->addCell($c[6], $cell(['borderRightSize' => $right]))
                    ->addText('', $tF, $cu);
            }
        }
    }

    private function totalHours($section, $records): void
    {
        $total = $records->sum(fn ($r) => $r->total_minutes ?? 0);
        $h = intdiv($total, 60);
        $m = $total % 60;

        $section->addText(
            sprintf("TOTAL NO. OF HOURS:\t\t\t\t%dh %02dm", $h, $m),
            ['name' => 'Arial', 'size' => 10, 'bold' => true],
            ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function certification($section): void
    {
        $section->addText(
            'I hereby certify on my honor that the above is a true and correct report of hours and assignments/tasks performed, a record of which was made daily at the time of arrival and departure from the office.',
            ['name' => 'Arial', 'size' => 7.5],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
    }

    private function signatures($section, string $studentName): void
    {
        $tabLeft = new \PhpOffice\PhpWord\Style\Tab('left', 0);
        $tabRight = new \PhpOffice\PhpWord\Style\Tab('right', Converter::inchToTwip(7.2));

        $pStyle = [
            'tabs' => [$tabLeft, $tabRight],
            'spaceAfter' => 0,
            'spaceBefore' => 0,
        ];

        $sigFont = ['name' => 'Arial', 'size' => 7.5];
        $sigBold = ['name' => 'Arial', 'size' => 7.5, 'bold' => true];
        $nameFont = ['name' => 'Arial', 'size' => 8, 'color' => '666666'];
        $uScore = str_repeat('_', 45);

        $section->addText(
            $uScore . "\t" . $uScore,
            $sigFont, $pStyle
        );

        $section->addText(
            "Student\u{2019}s Signature\t" . "HTE Supervisor\u{2019}s Name & Signature",
            $sigBold, $pStyle
        );

        $section->addText(
            $studentName . "\t",
            $nameFont, $pStyle
        );
    }
}
