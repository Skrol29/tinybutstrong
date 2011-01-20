<?php

class BenchmarkRunner {

	function BenchmarkRunner($runningCount = 0) {
		$this->setRunningCount($runningCount);
	}

	function getRunningCount() {
		return $this->_runningCount;
	}

	function setRunningCount($value) {
		$this->_runningCount = max(1, intval($value));
	}

	function run($name, $functionName, $params = null) {
		$beginTime = $this->getMicrotime();
		$beginMemory = $this->getMemory();
		$this->loop($functionName, $params);
		$endTime = $this->getMicrotime();
		$endMemory = $this->getMemory();
		$result = new BenchmarkResult();
		$result->name = $name;
		$result->runningCount = $this->getRunningCount();
		$result->totalTime = $endTime - $beginTime;
		$result->totalMemory = ($beginMemory!==false ? $endMemory - $beginMemory : false);
		return $result;
	}

	function loop($functionName, $params) {
		if ($params === null) $params = array();
		$runningCount = $this->getRunningCount();
		for ($i=0; $i<$runningCount; $i++)
			call_user_func_array($functionName, $params);
	}

	function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	function getMemory() {
		return (function_exists('memory_get_usage') ? memory_get_usage() : false);
	}
}

?>