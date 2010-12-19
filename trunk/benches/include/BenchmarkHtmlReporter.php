<?php

@include(dirname(__FILE__).'/tbs_class_php4.php');

class BenchmarkHtmlReporter {

	function getTemplateDir() {
		return dirname(__FILE__).'/templates/';
	}

	function getTemplateMerge($templateFileName, $vars = null) {
		$tbs = new clsTinyButStrong;
		$tbs->LoadTemplate($this->getTemplateDir().$templateFileName);
		foreach ($vars as $name => $value) {
			if (is_array($value))
				$tbs->MergeBlock($name, $value);
			else $tbs->MergeField($name, $value);
		}
		$tbs->Show(TBS_NOTHING);
		echo $tbs->Source;
	}

	function paintHeader($benchName) {
		$this->getTemplateMerge('header.html', array('benchName'=>$benchName, 'phpVersion'=>PHP_VERSION, 'phpOs'=>PHP_OS));
	}

	function paintFooter($benchName) {
		$this->getTemplateMerge('footer.html', array('benchName'=>$benchName));
	}

	function paintBenchResults($resultSet) {
		$this->getTemplateMerge('results.html', array('results'=>$resultSet->benchResults, 'resultSet'=>$resultSet));
	}

	function paintCompareResults($resultSet) {
		$this->getTemplateMerge('compare_results.html', array('results'=>$resultSet->compareResults));
	}

	function paintAll($resultSet) {
		$this->paintHeader($resultSet->name);
		$this->paintBenchResults($resultSet);
		$this->paintCompareResults($resultSet);
		$this->paintFooter($resultSet->name);
	}
}

?>