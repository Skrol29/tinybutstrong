<?php

// override unit test case class to simplify tinyButStrong test cases
class TBSUnitTestCase extends UnitTestCase {

	/**
	 * Last instance of 'clsTinyButStrong' class.
	 */
	var $tbs;

	/**
	 * Dump last merge result.
	 * @return string
	 */
	function dumpLastSource() {
		if (!is_null($this->tbs))
			$this->dump($this->tbs->Source);
	}

	/**
	 * Is tbs version at least the specified version ?
	 * @param string $versionString  a tbs version
	 * @return bool
	 */
	function atLeastTBSVersion($versionString) {
		$tbs = new clsTinyButStrong;
		return version_compare($tbs->Version, $versionString, '>=');
	}

	/**
	 * Skip tests case if the tbs version is not at least the specified version.
	 * @param string $versionString  a tbs version
	 * @param string $message        message to display (optional)
	 */
	function skipIfNotAtLeastTBSVersion($versionString, $message='%s') {
		$shouldSkip = !$this->atLeastTBSVersion($versionString);
		$this->skipIf($shouldSkip, $message);
	}

	/**
	 * Is PHP version at least the specified version ?
	 * @param string $versionString  a PHP version
	 * @return bool
	 */
	function atLeastPHPVersion($versionString) {
		return version_compare(PHP_VERSION, $versionString, '>=');
	}

	/**
	 * Skip tests case if the PHP version is not at least the specified version.
	 * @param string $versionString  a PHP version
	 * @param string $message        message to display (optional)
	 */
	function skipIfNotAtLeastPHPVersion($versionString, $message='%s') {
		$shouldSkip = !$this->atLeastPHPVersion($versionString);
		$this->skipIf($shouldSkip, $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeField
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeFieldStrings($source, $vars, $result, $message='%s') {
		$this->tbs = new clsTinyButStrong;
		$this->tbs->Source = $source;
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$this->tbs->MergeField($name, $value);
		$this->tbs->Show(TBS_NOTHING);
		return $this->assertEqual($this->tbs->Source, $result, $message);
	}

	/**
	 * Test TBS class with one function. Work only withe 'clear' and array data.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockStrings($source, $vars, $result, $message='%s') {
		$this->tbs = new clsTinyButStrong;
		$this->tbs->Source = $source;
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$this->tbs->MergeBlock($name, $value);
		$this->tbs->Show(TBS_NOTHING);
		return $this->assertEqual($this->tbs->Source, $result, $message);
	}

	/**
	 * Test TBS class with one function. Use 'num' parameter as second MergeBlock parameter.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeNumBlockStrings($source, $vars, $result, $message='%s') {
		$this->tbs = new clsTinyButStrong;
		$this->tbs->Source = $source;
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$this->tbs->MergeBlock($name, 'num', $value);
		$this->tbs->Show(TBS_NOTHING);
		return $this->assertEqual($this->tbs->Source, $result, $message);
	}

	/**
	 * Returns directory of HTML files to compare.
	 */
	function getTemplateDir() {
		return dirname(dirname(__FILE__)).'/template/';
	}

	/**
	 * Test TBS class with one function.
	 * @param string $sourceFilename  fine name of template source
	 * @param array $vars             associative array of name/value to pass to MergeBlock
	 * @param string $resultFilename  file name of merge result to compare
	 * @param string $message         message to display (optional)
	 * @return boolean                True on pass
	 */
	function assertEqualMergeFieldFiles($sourceFilename, $vars, $resultFilename, $message='%s') {
		$this->tbs = new clsTinyButStrong;
		$this->tbs->LoadTemplate($this->getTemplateDir().$sourceFilename);
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$this->tbs->MergeField($name, $value);
		$this->tbs->Show(TBS_NOTHING);
		return $this->assertEqual($this->tbs->Source, file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $sourceFilename fine name of template source
	 * @param array $vars            associative array of name/value to pass to MergeBlock
	 * @param string $resultFilename file name of merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockFiles($sourceFilename, $vars, $resultFilename, $message='%s') {
		$this->tbs = new clsTinyButStrong;
		$this->tbs->LoadTemplate($this->getTemplateDir().$sourceFilename);
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$this->tbs->MergeBlock($name, $value);
		$this->tbs->Show(TBS_NOTHING);
		return $this->assertEqual($this->tbs->Source, file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}
}

?>