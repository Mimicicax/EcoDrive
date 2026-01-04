<?php

namespace EcoDrive\Helpers\Statistics;

class LeastSquaresExtrapolator {
    private float $slope = 0;
    private float $yIntercept = 0;
    private $data = [];

    public function feed(float $x, float $y) {
        if (\array_key_exists($x, $this->data))
            $this->data[$x] += $y;

        else
            $this->data[$x] = $y;
    }

    public function finalise() {
        $xSum = 0;
        $xSquareSum = 0;
        $ySum = 0;
        $xyProductSum = 0;
        $pointCount = 0;

        foreach (array_keys($this->data) as $x) {
            $xSum += $x;
            $xSquareSum += $x * $x;
            $ySum += $this->data[$x];
            $xyProductSum += $x * $this->data[$x];
            $pointCount += 1;
        }

        $div = $pointCount * $xSquareSum - $xSum * $xSum;

        if ($div == 0) {
            $this->slope = 0;

            if (empty($this->data))
                $this->yIntercept = 0;

            else
                $this->yIntercept = $this->data[array_key_first($this->data)];

        } else {
            $this->slope = ($pointCount * $xyProductSum - $xSum * $ySum) / $div;
            $this->yIntercept = ($ySum - $this->slope * $xSum) / $pointCount;
        }
    }

    public function data() {
        return $this->data;
    }

    public function accumulate(float $a, float $b) {
        $end   = \max($a, $b);
        $start = \min($a, $b);

        return $this->slope * $end * $end / 2 + $this->yIntercept * $b - 
               $this->slope * $start * $start / 2 - $this->yIntercept * $start;
    }
}