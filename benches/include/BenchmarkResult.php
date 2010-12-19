<?php

class BenchmarkResult {

	var $name = '';
	var $runningCount = 0;
	var $totalTime = 0;
	var $totalMemory = 0;

	function clear() {
		$this->name = '';
		$this->runningCount = 0;
		$this->totalTime = 0;
		$this->totalMemory = 0;
	}

	function runningCountPrintable() {
		return number_format($this->runningCount, 0, '.', ' ').' times';
	}

	function totalTimePrintable() {
		return number_format($this->totalTime * 1000, 0, '.', ' ').' ms';
	}

	function totalMemoryPrintable() {
		if ($this->totalMemory === false) return 'not implemented';
		return number_format($this->totalMemory / 1024, 1, '.', ' ').' Ko';
	}
}

?>