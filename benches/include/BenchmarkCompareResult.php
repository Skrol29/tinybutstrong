<?php

class BenchmarkCompareResult {

	function BenchmarkCompareResult($faster, $slower) {
		$this->faster = $faster;
		$this->slower = $slower;
	}

	function ratePrintable() {
		$diffTime = $this->slower->totalTime - $this->faster->totalTime;
		return number_format(($diffTime / $this->slower->totalTime) * 100, 1, '.', ' ').'%';
	}
}

?>