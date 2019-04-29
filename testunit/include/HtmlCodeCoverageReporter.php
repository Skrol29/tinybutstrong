<?php

class HtmlCodeCoverageReporter extends HtmlReporter {

	var $code_coverage = '';
	var $code_coverage_include_paths = array();
	var $code_coverage_exclude_paths = array();
	var $time = 0;

	/**
	 * Initialize.
	 * @param mixed $includePaths      array or string of file or dir path to include in code coverage
	 * @param mixed $excludePaths      array or string of file or dir path to exclude from code coverage
	 */
	function __construct($includePaths, $excludePaths = null) {
		$this->HtmlReporter();
		if (is_array($includePaths)) {
			foreach ($includePaths as $includePath)
				$this->code_coverage_include_paths[] = $this->cleanDirecory($includePath);
		} elseif (is_string($includePaths))
			$this->code_coverage_include_paths[] = $this->cleanDirecory($includePaths);
		if (is_array($excludePaths)) {
			foreach ($excludePaths as $excludePath)
				$this->code_coverage_exclude_paths[] = $this->cleanDirecory($excludePath);
		} elseif (is_string($excludePaths))
			$this->code_coverage_exclude_paths[] = $this->cleanDirecory($excludePaths);
	}

	function acceptCoverageFile($fileName) {
		$found = FALSE;
		foreach ($this->code_coverage_include_paths as $include_path)
			if (substr($fileName, 0, strlen($include_path)) == $include_path && $include_path != '')
				$found = TRUE;
		foreach ($this->code_coverage_exclude_paths as $exclude_path)
			if (substr($fileName, 0, strlen($exclude_path)) == $exclude_path && $exclude_path != '')
				$found = FALSE;
		return $found;
	}

	function cleanDirecory($path) {
		return str_replace("/", DIRECTORY_SEPARATOR, str_replace("\\", DIRECTORY_SEPARATOR, $path));
	}

	function getMicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Paints the start of a group test. Will also paint
	 * the page header and footer if this is the
	 * first test. Will stash the size if the first start.
	 * @param string $test_name   Name of test that is starting.
	 * @param integer $size       Number of test cases starting.
	 */
	function paintGroupStart($test_name, $size) {
		$this->time = $this->getMicrotime();
		HtmlReporter::paintGroupStart($test_name, $size);
		if (extension_loaded('xdebug'))
			xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE); 
	}

	/**
	 * Paints the end of a group test. Will paint the page
	 * footer if the stack of tests has unwound.
	 * @param string $test_name   Name of test that is ending.
	 * @param integer $progress   Number of test cases ending.
	 */
	function paintGroupEnd($test_name) {
		if (extension_loaded('xdebug')) {
			$this->code_coverage = xdebug_get_code_coverage();
			xdebug_stop_code_coverage();
			ksort($this->code_coverage);
		}
		HtmlReporter::paintGroupEnd($test_name);
	}

	/**
	 * Paints the coverage data report for all tested files.
	 */
	function paintCodeCoverage() {
		print "<script type=\"text/javascript\">\nfunction toggle(id) {\n\tel = document.getElementById(id); el.style.display = el.style.display == 'none' ? '' : 'none';\n}\n</script>\n";
		print '<table id="coverage"><tr><th>File</th><th>Coverage Rate</th><th>Executed lines</th><th>Not executed lines</th><th>Dead lines</th></tr>';
		$fileCount = 0;
		$lineCountSum = 0;
		$deadLineCountSum = 0;
		$notExecutedLineCountSum = 0;
		$executedLineCountSum = 0;
		foreach ($this->code_coverage as $fileName => $coverageData) {
			if (!$this->acceptCoverageFile($fileName))
				continue;
			$fileCount++;
			$lineCount = 0;
			$deadLineCount = 0;
			$notExecutedLineCount = 0;
			$executedLineCount = 0;
			foreach ($coverageData as $status){
				if ($status == -2) $deadLineCount++;
				if ($status == 1) $executedLineCount++;
				if ($status == -1) $notExecutedLineCount++;
				$lineCount++;
			}
			$lineCountSum += $lineCount;
			$deadLineCountSum += $deadLineCount;
			$notExecutedLineCountSum += $notExecutedLineCount;
			$executedLineCountSum += $executedLineCount;
			print "<tr><td><a href=\"#\" onclick=\"toggle('code_coverage_$fileCount'); return false;\">".$fileName."</a></td>";
			$coverageRate = 0;
			if ($lineCount > 0)
				$coverageRate = round(($executedLineCount/($executedLineCount+$notExecutedLineCount)) * 100, 2);
			if ($coverageRate < 35) $css = "low";
			elseif ($coverageRate < 70) $css = "medium";
			else $css = "high";
			print "<td class=\"$css\">$coverageRate%</td><td class=\"$css\">$executedLineCount / $lineCount</td><td class=\"$css\">$notExecutedLineCount / $lineCount</td><td class=\"$css\">$deadLineCount / $lineCount</td></tr>";
			print "<tr><td colspan=\"5\" id=\"code_coverage_$fileCount\" style=\"display: none\">\n<pre class=\"source\">\n";
			$this->paintFileSource($fileName, $coverageData);
			print "</pre>\n</td></tr>\n";
		}
		$totalCoverageRate = round(($executedLineCountSum/($executedLineCountSum+$notExecutedLineCountSum)) * 100, 2);
		print "<tr><th>Total: $fileCount files</th><th>$totalCoverageRate%</th><th>$executedLineCountSum / $lineCountSum</th><th>$notExecutedLineCountSum / $lineCountSum</th><th>$deadLineCountSum / $lineCountSum</th></tr></table>";
	}

	/**
	 * Paints the source code file.
	 * @param string $fileName       Full path name of the file
	 * @param array $coverageData    Coverage data of this file
	 */
	function paintFileSource($fileName, $coverageData) {
		$lines  = explode("\n", str_replace("\t", '    ', file_get_contents($fileName)));
		if (count($lines) == 0) {
			print "Empty File\n";
			return;
		}
		$lines = array_map('rtrim', $lines);
		$linesLength = array_map('strlen', $lines);
		$width = max($linesLength);
		$i = 1;
		foreach ($lines as $line) {
			if (isSet($coverageData[$i])) {
				if ($coverageData[$i] == -2) $css = 'lineDeadCode';
				if ($coverageData[$i] == 1) $css = 'lineCov';
				if ($coverageData[$i] == -1) $css = 'lineNoCov';
			} else $css = '';
			$fillup = $width - strLen($line);
			if ($fillup > 0)
				$line .= str_repeat(' ', $fillup);
			$lineNum = sprintf('%6d', $i);
			print "<span class=\"lineNum\">$lineNum </span><span class=\"$css\">".htmlspecialchars($line)."</span>\n";
			$i++;
		}
	}

	/**
	 * Paints the end of the test with a summary of the passes and failures.
	 * @param string $test_name        Name class of test.
	 */
	function paintFooter($test_name) {
		$colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
		print "<div style=\"";
		print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
		print "\">";
		print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
		print " test cases complete:\n";
		print "<strong>" . $this->getPassCount() . "</strong> passes, ";
		print "<strong>" . $this->getFailCount() . "</strong> fails and ";
		print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
		print "</div>\n";
		$totalTime = $this->getMicrotime() - $this->time;
		print "<div style=\"padding: 8px;\">\n";
		print "<strong>Execution time: ".number_format($totalTime * 1000, 0, '.', ' ')." ms</strong>\n";
		print "</div>\n";
		print "<div style=\"padding: 8px;\">\n";
		print "<strong>Code coverage:</strong> ";
		if (!extension_loaded('xdebug'))
			print "Xdebug not loaded\n";
		else $this->paintCodeCoverage();
		print "</div>\n";
		print "</body>\n</html>\n";
	}

	/**
	 * Paints the CSS. Add additional styles here.
	 * @return string            CSS code as text.
	 * @access protected
	 */
	function _getCss() {
		return HtmlReporter::_getCss() .
			" pre.source { font-family: monospace; white-space: pre; background-color: #eeeeec; }" .
			" span.lineNum { background-color: #e9b96e; }" .
			" span.lineCov { background-color: #8ae234; }" .
			" span.lineNoCov { background-color: #FAEA4F; }" .
			" span.lineDeadCode { background-color: #EF7B00; }" .
			" table#coverage th { background-color: #555752; color: white; text-align: center; padding: 0.1em 1em; }" .
			" table#coverage td.a { color: black; }" .
			" table#coverage td.low { background-color: #ef7b00; color: black; text-align: center; padding: 0.1em 1em; }" .
			" table#coverage td.medium { background-color: #faea4f; color: black; text-align: center; padding: 0.1em 1em; }" .
			" table#coverage td.high { background-color: #8ee034; color: black; text-align: center; padding: 0.1em 1em; }" .
			" table#coverage td { background-color: #d4d7d0; color: black; text-align: left; padding: 0.1em 1em; }";
	}
	
}

?>