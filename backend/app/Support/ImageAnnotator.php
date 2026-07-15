<?php

namespace App\Support;

class ImageAnnotator
{
    public function annotate(string $inputPath, string $outputPath, array $annotations): void
    {
        if (!file_exists($inputPath)) {
            throw new \RuntimeException("Input image not found: $inputPath");
        }

        $img = imagecreatefrompng($inputPath);
        if (!$img) {
            $img = imagecreatefromjpeg($inputPath);
        }
        if (!$img) {
            throw new \RuntimeException("Cannot read image: $inputPath");
        }

        $width = imagesx($img);
        $height = imagesy($img);

        $red = imagecolorallocatealpha($img, 220, 38, 38, 0);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        $yellow = imagecolorallocatealpha($img, 255, 255, 0, 40);

        foreach ($annotations as $ann) {
            $x = (int) ($ann['x'] ?? 0);
            $y = (int) ($ann['y'] ?? 0);
            $number = (int) ($ann['number'] ?? 1);
            $label = $ann['label'] ?? (string) $number;
            $circleRadius = $ann['circle_radius'] ?? 24;
            $arrowEndX = $ann['arrow_end_x'] ?? null;
            $arrowEndY = $ann['arrow_end_y'] ?? null;

            $fontPath = $this->findBoldFont();
            $fontSize = $ann['font_size'] ?? 14;

            // Draw semi-transparent highlight box if specified
            if (isset($ann['highlight_x'], $ann['highlight_y'], $ann['highlight_w'], $ann['highlight_h'])) {
                imagefilledrectangle(
                    $img,
                    $ann['highlight_x'], $ann['highlight_y'],
                    $ann['highlight_x'] + $ann['highlight_w'], $ann['highlight_y'] + $ann['highlight_h'],
                    $yellow
                );
                imagerectangle(
                    $img,
                    $ann['highlight_x'], $ann['highlight_y'],
                    $ann['highlight_x'] + $ann['highlight_w'], $ann['highlight_y'] + $ann['highlight_h'],
                    $red
                );
            }

            // Draw arrow from circle to target
            if ($arrowEndX !== null && $arrowEndY !== null) {
                $this->drawArrow($img, $x, $y, $arrowEndX, $arrowEndY, $red, 3);
            }

            // Draw numbered circle
            imagefilledellipse($img, $x, $y, $circleRadius * 2, $circleRadius * 2, $red);
            imageellipse($img, $x, $y, $circleRadius * 2, $circleRadius * 2, $white);

            if ($fontPath) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $label);
                $textW = abs($bbox[2] - $bbox[0]);
                $textH = abs($bbox[7] - $bbox[1]);
                imagettftext($img, $fontSize, 0, $x - $textW / 2, $y + $textH / 2, $white, $fontPath, $label);
            } else {
                $fw = imagefontwidth(5) * strlen($label);
                $fh = imagefontheight(5);
                imagestring($img, 5, $x - $fw / 2, $y - $fh / 2, $label, $white);
            }

            // Draw label text below the circle if provided
            if (!empty($ann['text'])) {
                $textColor = imagecolorallocate($img, 220, 38, 38);
                $bgColor = imagecolorallocatealpha($img, 255, 255, 255, 60);
                $text = $ann['text'];

                if ($fontPath) {
                    $bbox = imagettfbbox(11, 0, $fontPath, $text);
                    $tw = abs($bbox[2] - $bbox[0]);
                    $th = abs($bbox[7] - $bbox[1]);
                    $tx = $x + $circleRadius + 8;
                    $ty = $y + $th / 2;

                    // Background rect
                    imagefilledrectangle($img, $tx - 2, $ty - $th - 2, $tx + $tw + 2, $ty + 2, $bgColor);
                    imagettftext($img, 11, 0, $tx, $ty, $textColor, $fontPath, $text);
                } else {
                    $tw = imagefontwidth(5) * strlen($text);
                    $tx = $x + $circleRadius + 4;
                    $ty = $y - imagefontheight(5) / 2;
                    imagefilledrectangle($img, $tx - 1, $ty - 1, $tx + $tw + 1, $ty + imagefontheight(5) + 1, $bgColor);
                    imagestring($img, 5, $tx, $ty, $text, $textColor);
                }
            }
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagepng($img, $outputPath);
        imagedestroy($img);
    }

    private function drawArrow($img, int $fromX, int $fromY, int $toX, int $toY, $color, int $thickness): void
    {
        imagesetthickness($img, $thickness);
        imageline($img, $fromX, $fromY, $toX, $toY, $color);

        $angle = atan2($toY - $fromY, $toX - $fromX);
        $arrowLen = 12;
        $arrowAngle = deg2rad(25);

        $ax1 = $toX - $arrowLen * cos($angle - $arrowAngle);
        $ay1 = $toY - $arrowLen * sin($angle - $arrowAngle);
        $ax2 = $toX - $arrowLen * cos($angle + $arrowAngle);
        $ay2 = $toY - $arrowLen * sin($angle + $arrowAngle);

        imagefilledpolygon($img, [
            $toX, $toY,
            (int) $ax1, (int) $ay1,
            (int) $ax2, (int) $ay2,
        ], 3, $color);
    }

    private function findBoldFont(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/ARIALBD.TTF',
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/ARIAL.TTF',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans-Bold.ttf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
