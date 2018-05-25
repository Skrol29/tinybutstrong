<?php

class BenchmarkCompareResult
{
    public function BenchmarkCompareResult($faster, $slower)
    {
        $this->faster = $faster;
        $this->slower = $slower;
    }

    public function ratePrintable()
    {
        $diffTime = $this->slower->totalTime - $this->faster->totalTime;
        return number_format(($diffTime / $this->slower->totalTime) * 100, 1, '.', ' ').'%';
    }
}
