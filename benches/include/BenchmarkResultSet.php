<?php

class BenchmarkResultSet extends BenchmarkResult
{
    public function BenchmarkResultSet($name = '')
    {
        $this->clear();
        $this->name = $name;
    }

    public function clear()
    {
        BenchmarkResult::clear();
        $this->benchResults = array();
        $this->compareResults = array();
    }

    public function add($result)
    {
        $this->benchResults[] = $result;
        $this->runningCount += $result->runningCount;
        $this->totalTime += $result->totalTime;
        $this->totalMemory = ($result->totalMemory!==false ? $this->totalMemory + $result->totalMemory : false);
    }

    public function compare($result1, $result2)
    {
        $faster = ($result1->totalTime <= $result2->totalTime ? $result1 : $result2);
        $slower = ($result1->totalTime <= $result2->totalTime ? $result2 : $result1);
        $this->compareResults[] = new BenchmarkCompareResult($faster, $slower);
    }
}
