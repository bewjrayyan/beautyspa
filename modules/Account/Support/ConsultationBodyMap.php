<?php

namespace Modules\Account\Support;

use Modules\Account\Exceptions\ConsultationPdfException;

class ConsultationBodyMap
{
    public static function positions(): array
    {
        return [
            'Kepala / Head' => [[14.2, 9], [36.8, 9], [62.2, 9], [84.6, 9]],
            'Muka / Face' => [[14.2, 12], [62.2, 12]],
            'Leher / Neck' => [[14.2, 18], [36.8, 18], [62.2, 18], [84.6, 18]],
            'Bahu / Shoulders' => [[18.5, 23], [41.1, 23], [66.2, 23], [88.6, 23]],
            'Dada & payudara / Chest & breasts' => [[14.2, 31], [62.2, 31]],
            'Perut / Abdomen' => [[14.2, 40], [62.2, 40]],
            'Belakang atas / Upper back' => [[36.8, 31], [84.6, 31]],
            'Pinggang & belakang bawah / Waist & lower back' => [[36.8, 43], [84.6, 43]],
            'Lengan atas / Upper arms' => [[20.7, 32], [43.3, 32], [68.2, 32], [90.6, 32]],
            'Siku / Elbows' => [[21.8, 41], [44.4, 41], [69.3, 41], [91.7, 41]],
            'Lengan bawah / Forearms' => [[22.6, 46], [45.2, 46], [70.1, 46], [92.5, 46]],
            'Pergelangan tangan / Wrists' => [[23.5, 50], [46.1, 50], [71, 50], [93.4, 50]],
            'Tangan & jari / Hands & fingers' => [[25, 53], [47.6, 53], [72.5, 53], [94.9, 53]],
            'Pinggul & pelvis / Hips & pelvis' => [[14.2, 49], [36.8, 49], [62.2, 49], [84.6, 49]],
            'Punggung / Buttocks' => [[36.8, 53], [84.6, 53]],
            'Pangkal paha / Groin' => [[14.2, 53], [62.2, 53]],
            'Paha / Thighs' => [[16.6, 61], [39.2, 61], [64.6, 61], [87, 61]],
            'Lutut / Knees' => [[16.4, 70], [39, 70], [64.4, 70], [86.8, 70]],
            'Betis & tulang kering / Calves & shins' => [[16.4, 80], [39, 80], [64.4, 80], [86.8, 80]],
            'Buku lali / Ankles' => [[16.4, 91], [39, 91], [64.4, 91], [86.8, 91]],
            'Tumit / Heels' => [[39, 95], [86.8, 95]],
            'Bahagian atas kaki / Top of feet' => [[16.4, 95], [64.4, 95]],
            'Tapak & jari kaki / Soles & toes' => [[14.7, 97], [37.3, 97], [62.7, 97], [85.1, 97]],
            'Leher & bahu / Neck & shoulders' => [[18.5, 22], [41.1, 22], [66.2, 22], [88.6, 22]],
            'Dada / Chest' => [[14.2, 31], [62.2, 31]],
            'Belakang / Back' => [[36.8, 34], [84.6, 34]],
            'Pinggang / Lower back' => [[36.8, 43], [84.6, 43]],
            'Lengan / Arms' => [[21.2, 36], [43.8, 36], [68.7, 36], [91.2, 36]],
            'Tangan / Hands' => [[25, 53], [47.6, 53], [72.5, 53], [94.9, 53]],
            'Pinggul / Hips' => [[14.2, 49], [36.8, 49], [62.2, 49], [84.6, 49]],
            'Kaki / Legs' => [[16.4, 74], [39, 74], [64.4, 74], [86.8, 74]],
            'Tapak kaki / Feet' => [[14.7, 96], [37.3, 96], [62.7, 96], [85.1, 96]],
        ];
    }

    public static function annotatedSvgDataUri(array $selectedAreas, array $options): string
    {
        $svg = static::loadSvg();
        $markers = static::buildAnnotations($selectedAreas, $options);
        $svg = str_replace('</svg>', $markers . '</svg>', $svg);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private static function loadSvg(): string
    {
        $path = public_path('images/consultation/anatomi_badan_depan_belakang.svg');
        $svg = @file_get_contents($path);

        if ($svg === false || ! str_contains($svg, '</svg>')) {
            throw new ConsultationPdfException("Unable to load consultation body-map SVG: {$path}");
        }

        return $svg;
    }

    private static function buildAnnotations(array $selectedAreas, array $options): string
    {
        $output = static::arrowDefinition() . '<g aria-label="Selected body areas">';
        $positions = static::positions();

        foreach ($selectedAreas as $area) {
            $areaPositions = $positions[$area] ?? [];
            $number = static::optionNumber($area, $options);
            $output .= static::markerElements($areaPositions, $number);
            $output .= static::calloutElement($area, $areaPositions, $number);
        }

        return $output . '</g>';
    }

    private static function arrowDefinition(): string
    {
        return '<defs><marker id="consultation-callout-arrow" viewBox="0 0 10 10" '
            . 'refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">'
            . '<path d="M 0 0 L 10 5 L 0 10 z" fill="#b50863"/></marker></defs>';
    }

    private static function optionNumber(string $area, array $options): string
    {
        $index = array_search($area, $options, true);

        return $index === false ? '?' : (string) ($index + 1);
    }

    private static function markerElements(array $positions, string $number): string
    {
        $output = '';

        foreach ($positions as [$left, $top]) {
            [$x, $y] = static::coordinates($left, $top);
            $output .= '<circle cx="' . $x . '" cy="' . $y
                . '" r="18" fill="#b50863" stroke="#ffffff" stroke-width="4"/>';
            $output .= '<text x="' . $x . '" y="' . ($y + 6)
                . '" fill="#ffffff" font-family="DejaVu Sans, sans-serif" font-size="18" '
                . 'font-weight="700" text-anchor="middle">' . $number . '</text>';
        }

        return $output;
    }

    private static function calloutElement(string $area, array $positions, string $number): string
    {
        $position = static::calloutPosition($positions);

        if ($position === null) {
            return '';
        }

        [$left, $top] = $position;
        [$x, $y] = static::coordinates($left, $top);
        $layout = static::calloutLayout($left, $x, $y);
        [$primary, $secondary] = static::labels($area);

        return static::calloutLine($layout, $y)
            . static::calloutBox($layout, $number, $primary, $secondary);
    }

    private static function calloutPosition(array $positions): ?array
    {
        foreach ($positions as $position) {
            if ($position[0] >= 55 && $position[0] <= 88) {
                return $position;
            }
        }

        return $positions[0] ?? null;
    }

    private static function coordinates(float|int $left, float|int $top): array
    {
        return [
            round(1536 * $left / 100, 2),
            round(1024 * $top / 100, 2),
        ];
    }

    private static function calloutLayout(float|int $left, float $x, float $y): array
    {
        $placeLeft = $left > 72;
        $labelWidth = 250;
        $labelX = $placeLeft ? max(8, $x - 310) : min(1278, $x + 60);

        return [
            'label_width' => $labelWidth,
            'label_x' => $labelX,
            'label_y' => max(8, min(978, $y - 20)),
            'line_x' => $placeLeft ? $labelX + $labelWidth : $labelX,
            'target_x' => $placeLeft ? $x - 25 : $x + 25,
        ];
    }

    private static function labels(string $area): array
    {
        return array_map(
            fn (string $label): string => htmlspecialchars(
                trim($label),
                ENT_XML1 | ENT_QUOTES,
                'UTF-8'
            ),
            array_pad(explode('/', $area, 2), 2, '')
        );
    }

    private static function calloutLine(array $layout, float $y): string
    {
        return '<line x1="' . $layout['line_x'] . '" y1="' . $y . '" x2="' . $layout['target_x']
            . '" y2="' . $y . '" stroke="#b50863" stroke-width="2" '
            . 'marker-end="url(#consultation-callout-arrow)"/>';
    }

    private static function calloutBox(
        array $layout,
        string $number,
        string $primary,
        string $secondary
    ): string {
        $x = $layout['label_x'];
        $y = $layout['label_y'];
        $output = '<rect x="' . $x . '" y="' . $y . '" width="' . $layout['label_width']
            . '" height="40" rx="7" fill="#ffffff" fill-opacity="0.96" '
            . 'stroke="#d9a9c0" stroke-width="1.5"/>';
        $output .= '<circle cx="' . ($x + 20) . '" cy="' . ($y + 20) . '" r="12" fill="#b50863"/>';
        $output .= '<text x="' . ($x + 20) . '" y="' . ($y + 25)
            . '" fill="#ffffff" font-family="DejaVu Sans, sans-serif" font-size="13" '
            . 'font-weight="700" text-anchor="middle">' . $number . '</text>';
        $output .= '<text x="' . ($x + 40) . '" y="' . ($y + 17)
            . '" fill="#790442" font-family="DejaVu Sans, sans-serif" font-size="12" '
            . 'font-weight="700">' . $primary . '</text>';

        if ($secondary !== '') {
            $output .= '<text x="' . ($x + 40) . '" y="' . ($y + 31)
                . '" fill="#866f7b" font-family="DejaVu Sans, sans-serif" font-size="10">'
                . $secondary . '</text>';
        }

        return $output;
    }
}
