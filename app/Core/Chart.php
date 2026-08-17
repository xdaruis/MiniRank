<?php

declare(strict_types=1);

namespace App\Core;

class Chart
{
    private const WIDTH = 860;
    private const HEIGHT = 260;
    private const PAD = 30;
    private const ACCENT = '#2f6f4f';

    public static function line(array $rows): string
    {
        if (count($rows) < 2) {
            return '<p class="text-muted">Not enough data for a chart.</p>';
        }

        $positions = array_map(fn ($r) => (int) $r['position'], $rows);
        $min = min($positions);
        $max = max($positions);
        $span = max(1, $max - $min);
        $pad = max(1, (int) round($span * 0.1));

        $lo = min(1, $min - $pad);
        $hi = max(100, $max + $pad);
        $range = $hi - $lo;

        $n = count($rows);
        $plotW = self::WIDTH - 2 * self::PAD;
        $plotH = self::HEIGHT - 2 * self::PAD;
        $plots = [];
        foreach ($rows as $i => $r) {
            $x = self::PAD + $i * $plotW / ($n - 1);
            $y = self::PAD + ((int) $r['position'] - $lo) / $range * $plotH;
            $plots[] = ['x' => round($x, 1), 'y' => round($y, 1)];
        }

        $body = self::smoothPath($plots);
        $base = self::HEIGHT - self::PAD;
        $area = 'M' . $plots[0]['x'] . ',' . $plots[0]['y']
            . ' ' . $body
            . ' L' . $plots[$n - 1]['x'] . ',' . $base
            . ' L' . $plots[0]['x'] . ',' . $base . ' Z';

        $grad = '<defs><linearGradient id="chart-grad" x1="0" y1="0" x2="0" y2="1">'
            . '<stop offset="0%" stop-color="' . self::ACCENT . '" stop-opacity="0.18"/>'
            . '<stop offset="100%" stop-color="' . self::ACCENT . '" stop-opacity="0"/>'
            . '</linearGradient></defs>';

        $axis = '<line x1="' . self::PAD . '" y1="' . self::PAD . '" x2="' . self::PAD . '" y2="' . $base . '" class="chart-axis"/>'
            . '<line x1="' . self::PAD . '" y1="' . $base . '" x2="' . (self::WIDTH - self::PAD) . '" y2="' . $base . '" class="chart-axis"/>';

        $grids = '';
        foreach ([1 / 3, 2 / 3] as $frac) {
            $y = self::PAD + $frac * $plotH;
            $val = (int) round($lo + $frac * $range);
            $grids .= '<line x1="' . self::PAD . '" y1="' . round($y, 1) . '" x2="' . (self::WIDTH - self::PAD) . '" y2="' . round($y, 1) . '" class="chart-grid"/>'
                . '<text x="' . (self::PAD - 6) . '" y="' . round($y + 4, 1) . '" text-anchor="end" class="chart-label">' . $val . '</text>';
        }

        $labels = '<text x="' . (self::PAD - 6) . '" y="' . (self::PAD + 4) . '" text-anchor="end" class="chart-label">' . $lo . '</text>'
            . '<text x="' . (self::PAD - 6) . '" y="' . ($base + 4) . '" text-anchor="end" class="chart-label">' . $hi . '</text>';

        $points = '';
        foreach ($plots as $i => $p) {
            $tip = '<title>' . Response::e((string) $rows[$i]['captured_at'])
                . ' · Position ' . $positions[$i] . '</title>';
            $attrs = 'data-date="' . Response::e((string) $rows[$i]['captured_at'])
                . '" data-position="' . $positions[$i] . '"';
            if ($i === $n - 1) {
                $points .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="4" class="chart-point-current"/>'
                    . '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="7" ' . $attrs . ' class="chart-hit">' . $tip . '</circle>'
                    . '<text x="' . $p['x'] . '" y="' . ($p['y'] - 8) . '" text-anchor="middle" class="chart-label">'
                    . $positions[$i] . '</text>';
            } else {
                $points .= '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="3" class="chart-point"/>'
                    . '<circle cx="' . $p['x'] . '" cy="' . $p['y'] . '" r="7" ' . $attrs . ' class="chart-hit">' . $tip . '</circle>';
            }
        }

        return '<svg class="chart" viewBox="0 0 ' . self::WIDTH . ' ' . self::HEIGHT
            . '" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" aria-label="Position trend">'
            . $grad
            . '<path d="' . $area . '" class="chart-area"/>'
            . $axis . $grids . $labels
            . '<path d="' . $body . '" class="chart-line" stroke-linejoin="round" stroke-linecap="round"/>'
            . $points
            . '</svg>';
    }

    private static function smoothPath(array $plots): string
    {
        $d = 'M' . $plots[0]['x'] . ',' . $plots[0]['y'];
        for ($i = 1; $i < count($plots); $i++) {
            $prev = $plots[$i - 1];
            $cur = $plots[$i];
            $midX = ($prev['x'] + $cur['x']) / 2;
            $d .= ' Q' . round($midX, 1) . ',' . $prev['y'] . ' ' . $cur['x'] . ',' . $cur['y'];
        }
        return $d;
    }
}
