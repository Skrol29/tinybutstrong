<?php

include(dirname(__FILE__).'/BenchmarkRunner.php');
include(dirname(__FILE__).'/BenchmarkResult.php');
include(dirname(__FILE__).'/BenchmarkResultSet.php');
include(dirname(__FILE__).'/BenchmarkReporter.php');
include(dirname(__FILE__).'/BenchmarkHtmlReporter.php');
include(dirname(__FILE__).'/BenchmarkCompareResult.php');

define('BENCHMARK_DEFAULT_RUNNING_COUNT', 1000);

class Benchmark
{
    public function Benchmark($benchName = '')
    {
        $this->_runner = new BenchmarkRunner(BENCHMARK_DEFAULT_RUNNING_COUNT);
        $this->_reporter = new BenchmarkHtmlReporter();
        $this->_resultSet = new BenchmarkResultSet($benchName);
    }

    public function getRunningCount()
    {
        return $this->_runner->getRunningCount();
    }

    public function setRunningCount($value)
    {
        $this->_runner->setRunningCount($value);
    }

    public function run($name, $functionName, $params = null)
    {
        $result = $this->_runner->run($name, $functionName, $params);
        $this->_resultSet->add($result);
        return $result;
    }

    public function compare($result1, $result2)
    {
        if (is_a($result1, 'BenchmarkResult') && is_a($result2, 'BenchmarkResult')) {
            $this->_resultSet->compare($result1, $result2);
        }
    }

    public function showResults()
    {
        $this->_reporter->paintAll($this->_resultSet);
    }
}
