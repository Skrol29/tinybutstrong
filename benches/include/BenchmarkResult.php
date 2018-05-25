<?php

class BenchmarkResult
{
    public $name = '';
    public $runningCount = 0;
    public $totalTime = 0;
    public $totalMemory = 0;

    public function clear()
    {
        $this->name = '';
        $this->runningCount = 0;
        $this->totalTime = 0;
        $this->totalMemory = 0;
    }

    public function runningCountPrintable()
    {
        return number_format($this->runningCount, 0, '.', ' ').' times';
    }

    public function totalTimePrintable()
    {
        return number_format($this->totalTime * 1000, 0, '.', ' ').' ms';
    }

    public function totalMemoryPrintable()
    {
        if ($this->totalMemory === false) {
            return 'not implemented';
        }
        return number_format($this->totalMemory / 1024, 1, '.', ' ').' Ko';
    }
}
