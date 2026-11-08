<?php

namespace App\Services;

class BarcodeService
{
    /**
     * Code 128 Character patterns (Table B)
     * Each pattern is an 11-module string of '1' (bar) and '0' (space).
     */
    private static array $code128Table = [
        0 => '11011001100', 1 => '11001101100', 2 => '11001100110', 3 => '10010011000',
        4 => '10010001100', 5 => '10001001100', 6 => '10011001000', 7 => '10011000100',
        8 => '10001100100', 9 => '11001001000', 10 => '11001000100', 11 => '11000100100',
        12 => '10110011100', 13 => '10011011100', 14 => '10011001110', 15 => '10111001100',
        16 => '10011101100', 17 => '10011100110', 18 => '11001110010', 19 => '11001011100',
        20 => '11001001110', 21 => '11011100100', 22 => '11001110100', 23 => '11101101110',
        24 => '11101001100', 25 => '11100101100', 26 => '11100100110', 27 => '11101100100',
        28 => '11100110100', 29 => '11100110010', 30 => '11011011000', 31 => '11011000110',
        32 => '11000110110', 33 => '10100011000', 34 => '10001011000', 35 => '10001000110',
        36 => '10110001000', 37 => '10001101000', 38 => '10001100010', 39 => '11010001000',
        40 => '11000101000', 41 => '11000100010', 42 => '10110111000', 43 => '10110001110',
        44 => '10001101110', 45 => '10111011000', 46 => '10111000110', 47 => '10001110110',
        48 => '11101110110', 49 => '11010001110', 50 => '11000101110', 51 => '11011101000',
        52 => '11011100010', 53 => '11011101110', 54 => '11101011000', 55 => '11101000110',
        56 => '11100010110', 57 => '11101101000', 58 => '11101100010', 59 => '11100011010',
        60 => '11101111010', 61 => '11001000010', 62 => '11110001010', 63 => '10100110000',
        64 => '10100001100', 65 => '10010110000', 66 => '10010000110', 67 => '10000101100',
        68 => '10000100110', 69 => '10110010000', 70 => '10110000100', 71 => '10011010000',
        72 => '10011000010', 73 => '10000110100', 74 => '10000110010', 75 => '11000010010',
        76 => '11001010000', 77 => '11110111010', 78 => '11000010100', 79 => '10001111010',
        80 => '10100111100', 81 => '10010111100', 82 => '10010011110', 83 => '10111100100',
        84 => '10011110100', 85 => '10011110010', 86 => '11110100100', 87 => '11110010100',
        88 => '11110010010', 89 => '11011011110', 90 => '11011110110', 91 => '11110110110',
        92 => '10101111000', 93 => '10100011110', 94 => '10001011110', 95 => '10111101000',
        96 => '10111100010', 97 => '11110101000', 98 => '11110100010', 99 => '10111011110',
        100 => '10111101110', 101 => '11101011110', 102 => '11110101110', 103 => '11010000100', // START A
        104 => '11010010000', // START B (Standard ASCII 32-126)
        105 => '11010011100', // START C
        106 => '1100011101011', // STOP (13 modules)
    ];

    /**
     * Generate pure SVG string for a Code 128 barcode.
     */
    public static function generateBarcodeSvg(string $text, int $width = 300, int $height = 70, bool $showText = true): string
    {
        $cleanText = trim($text);
        if ($cleanText === '') {
            $cleanText = 'CTZ-000000';
        }

        // Start B symbol is 104
        $startCode = 104;
        $checksum = $startCode;
        $modules = self::$code128Table[$startCode];

        $chars = str_split($cleanText);
        foreach ($chars as $index => $char) {
            $ascii = ord($char);
            // In Code 128 B, character value = ASCII - 32
            $codeValue = $ascii - 32;
            if ($codeValue < 0 || $codeValue > 95) {
                $codeValue = 0; // fallback to space
            }

            $checksum += $codeValue * ($index + 1);
            $modules .= self::$code128Table[$codeValue];
        }

        // Checksum code
        $checkCode = $checksum % 103;
        $modules .= self::$code128Table[$checkCode];

        // Stop code
        $modules .= self::$code128Table[106];

        $totalModules = strlen($modules);
        $barHeight = $showText ? ($height - 18) : $height;
        $moduleWidth = $width / $totalModules;

        $svgBars = '';
        $inBar = false;
        $barStart = 0;

        for ($i = 0; $i < $totalModules; $i++) {
            if ($modules[$i] === '1') {
                if (!$inBar) {
                    $inBar = true;
                    $barStart = $i;
                }
            } else {
                if ($inBar) {
                    $barWidth = ($i - $barStart) * $moduleWidth;
                    $x = $barStart * $moduleWidth;
                    $svgBars .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#111827" />', $x, $barWidth, $barHeight);
                    $inBar = false;
                }
            }
        }

        if ($inBar) {
            $barWidth = ($totalModules - $barStart) * $moduleWidth;
            $x = $barStart * $moduleWidth;
            $svgBars .= sprintf('<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#111827" />', $x, $barWidth, $barHeight);
        }

        $textElement = '';
        if ($showText) {
            $textY = $height - 4;
            $textElement = sprintf(
                '<text x="50%%" y="%d" text-anchor="middle" font-family="monospace, monospace" font-size="12" font-weight="700" fill="#111827" letter-spacing="2">%s</text>',
                $textY,
                htmlspecialchars($cleanText)
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="%d" height="%d" style="background:#ffffff; max-width:100%%; height:auto; display:block; margin:0 auto;">%s%s</svg>',
            $width,
            $height,
            $width,
            $height,
            $svgBars,
            $textElement
        );
    }

    /**
     * A real, scannable QR code holding the waybill's tracking code, so riders
     * can scan either the barcode or the QR code.
     */
    public static function generateQrSvg(string $text, int $size = 100): string
    {
        $cleanText = trim($text) !== '' ? trim($text) : 'CTZ-000000';
        $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        ));
        $svg = $writer->writeString($cleanText);

        // Drop the XML prolog so the SVG can sit inline in the waybill HTML.
        return preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
    }
}
