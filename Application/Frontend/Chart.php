<?php
/**
 * Tales of Souls
 *
 * PHP Version 8.2
 *
 * @package   Application\Frontend
 * @version   1.0.0
 */
declare(strict_types=1);

namespace Application\Frontend;

/**
 * Server side SVG line charts (no JavaScript).
 *
 * Hover information is provided with SVG <title> elements on enlarged hit targets.
 */
final class Chart
{
    /**
     * Render a line chart
     *
     * @param array $series  List of ['label' => string, 'class' => string, 'step' => bool,
     *                       'points' => [['x' => float, 'y' => float, 'title' => string, 'marker' => ?string], ...]]
     * @param array $options ['xMax' => float, 'yMax' => float, 'yTicks' => float[], 'yFormat' => callable,
     *                       'xTicks' => float[], 'xFormat' => callable, 'label' => string]
     *
     * @return string
     */
    public static function line(array $series, array $options) : string
    {
        $w      = 640;
        $h      = 220;
        $left   = 44;
        $right  = 96;
        $top    = 12;
        $bottom = 26;
        $plotW  = $w - $left - $right;
        $plotH  = $h - $top - $bottom;

        $xMin    = $options['xMin'] ?? 0;
        $xMax    = \max($xMin + 1, $options['xMax']);
        $yMax    = \max(1, $options['yMax']);
        $yFormat = $options['yFormat'] ?? fn ($v) => (string) $v;
        $xFormat = $options['xFormat'] ?? fn ($v) => (string) $v;

        $sx = fn ($x) => \round($left + ($x - $xMin) / ($xMax - $xMin) * $plotW, 2);
        $sy = fn ($y) => \round($top + $plotH - $y / $yMax * $plotH, 2);

        $e = fn ($text) => \htmlspecialchars((string) $text, \ENT_QUOTES);

        $svg = '<svg class="chart line-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . $e($options['label'] ?? 'Chart') . '">';

        // grid & y axis
        foreach ($options['yTicks'] ?? [0, $yMax] as $tick) {
            $y    = $sy($tick);
            $svg .= '<line class="grid" x1="' . $left . '" x2="' . ($left + $plotW) . '" y1="' . $y . '" y2="' . $y . '"/>';
            $svg .= '<text class="axis" x="' . ($left - 6) . '" y="' . ($y + 3) . '" text-anchor="end">' . $e($yFormat($tick)) . '</text>';
        }

        // x axis
        foreach ($options['xTicks'] ?? [$xMin, $xMax] as $tick) {
            $svg .= '<text class="axis" x="' . $sx($tick) . '" y="' . ($h - 8) . '" text-anchor="middle">' . $e($xFormat($tick)) . '</text>';
        }

        $svg .= '<line class="baseline" x1="' . $left . '" x2="' . ($left + $plotW) . '" y1="' . ($top + $plotH) . '" y2="' . ($top + $plotH) . '"/>';

        // series
        foreach ($series as $s) {
            $points = $s['points'];
            if (empty($points)) {
                continue;
            }

            $d = '';
            foreach ($points as $i => $p) {
                if ($i === 0) {
                    $d .= 'M' . $sx($p['x']) . ',' . $sy($p['y']);
                } elseif ($s['step'] ?? false) {
                    $d .= ' H' . $sx($p['x']) . ' V' . $sy($p['y']);
                } else {
                    $d .= ' L' . $sx($p['x']) . ',' . $sy($p['y']);
                }
            }

            if (($s['step'] ?? false) && isset($options['xEnd'])) {
                $d .= ' H' . $sx($options['xEnd']);
            }

            $svg .= '<path class="line ' . $e($s['class']) . '" d="' . $d . '"/>';

            // markers only while they stay readable, always on the last point
            $showAll = \count($points) <= 25 && !($s['step'] ?? false);
            foreach ($points as $i => $p) {
                $isLast = $i === \count($points) - 1;
                $cx     = $sx($p['x']);
                $cy     = $sy($p['y']);

                $svg .= '<g class="point"><title>' . $e($p['title'] ?? '') . '</title>';
                $svg .= '<circle class="hit" cx="' . $cx . '" cy="' . $cy . '" r="10"/>';

                if ($showAll || $isLast || isset($p['marker'])) {
                    $svg .= '<circle class="marker ' . $e($s['class']) . ' ' . $e($p['marker'] ?? '') . '" cx="' . $cx . '" cy="' . $cy . '" r="4"/>';
                }

                $svg .= '</g>';
            }

            // direct label at the end of the line
            $last  = \end($points);
            $lastX = ($s['step'] ?? false) && isset($options['xEnd']) ? $options['xEnd'] : $last['x'];
            $svg  .= '<text class="direct-label" x="' . ($sx($lastX) + 8) . '" y="' . ($sy($last['y']) + 4 + ($s['labelOffset'] ?? 0)) . '">'
                . $e($s['label']) . '</text>';
        }

        return $svg . '</svg>';
    }

    /**
     * Nice x axis ticks for 1..n (max ~10 labels)
     *
     * @param int $count Amount of values
     *
     * @return int[]
     */
    public static function countTicks(int $count) : array
    {
        $step = \max(1, (int) \ceil($count / 10));

        $ticks = \range(1, $count, $step);
        if (\end($ticks) !== $count) {
            if ($count - \end($ticks) < $step / 2) {
                \array_pop($ticks);
            }

            $ticks[] = $count;
        }

        return $ticks;
    }
}
