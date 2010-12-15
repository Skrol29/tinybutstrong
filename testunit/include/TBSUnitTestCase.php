<?php

/* Versionning
Skrol29, 2010-12-13: add the isBug() feature on assertEqualMergeFieldStrings()
Skrol29, 2010-12-14: add the isBug() feature on assertEqualMergeBlockStrings() + TBS_TEST_DEBUGMODE
Skrol29, 2010-12-15: replace the bug swtich with a condition on PHP/TBS version
*/

define('TBS_TEST_DEBUGMODE','DEBUGMODE');  // display the actual result and exit all tests

// override unit test case class to simplify tinyButStrong test cases
class TBSUnitTestCase extends UnitTestCase {

	/**
	 * Skip tests case if the tbs version is not at least the specified version.
	 * @param string $versionString  a tbs version
	 * @param string $message        message to display (optional)
	 */
	function skipIfNotAtLeastVersion($versionString, $message='%s') {
		$tbs = new clsTinyButStrong;
		$shouldSkip = version_compare($tbs->Version, $versionString, '<');
		$this->skipIf($shouldSkip, $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeField
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @param string $condition      false, or an expression about TBS or PHP version. Example: 'TBS<=3.5.1' or 'PHP>3.0.0'
	 * @return boolean               True on pass
	 */
	function assertEqualMergeFieldStrings($source, $vars = null, $result, $message='%s', $condition=false) {
		$tbs = new clsTinyButStrong;
		$tbs->Source = $source;
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$tbs->MergeField($name, $value);
		$tbs->Show(TBS_NOTHING);
		if ($condition===TBS_TEST_DEBUGMODE) exit($tbs->Source);
		if ($this->isAppropriate($tbs,$condition)) return $this->assertEqual($tbs->Source, $result, $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @param string $condition      false, or an expression about TBS or PHP version. Example: 'TBS<=3.5.1' or 'PHP>3.0.0'
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockStrings($source, $vars = null, $result, $message='%s', $condition=false) {
		$tbs = new clsTinyButStrong;
		$tbs->Source = $source;
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				if (is_int($value)) {
					$tbs->MergeBlock($name, 'num', $value);
				} else {
					$tbs->MergeBlock($name, $value); // works only with 'clear' and arrays, otherwise a third argument is needed
				}
			}
		}
		$tbs->Show(TBS_NOTHING);
		if ($condition===TBS_TEST_DEBUGMODE) exit($tbs->Source);
		if ($this->isAppropriate($tbs,$condition)) return $this->assertEqual($tbs->Source, $result, $message);
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
	 * @param string $condition      false, or an expression about TBS or PHP version. Example: 'TBS<=3.5.1' or 'PHP>3.0.0'
	 * @return boolean                True on pass
	 */
	function assertEqualMergeFieldFiles($sourceFilename, $vars = null, $resultFilename, $message='%s', $condition=false) {
		$tbs = new clsTinyButStrong;
		$tbs->LoadTemplate($this->getTemplateDir().$sourceFilename);
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$tbs->MergeField($name, $value);
		$tbs->Show(TBS_NOTHING);
		if ($condition===TBS_TEST_DEBUGMODE) exit($tbs->Source);
		if ($this->isAppropriate($tbs,$condition)) return $this->assertEqual($tbs->Source, file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $sourceFilename fine name of template source
	 * @param array $vars            associative array of name/value to pass to MergeBlock
	 * @param string $resultFilename file name of merge result to compare
	 * @param string $message        message to display (optional)
	 * @param string $condition      false, or an expression about TBS or PHP version. Example: 'TBS<=3.5.1' or 'PHP>3.0.0'
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockFiles($sourceFilename, $vars = null, $resultFilename, $message='%s', $condition=false) {
		$tbs = new clsTinyButStrong;
		$tbs->LoadTemplate($this->getTemplateDir().$sourceFilename);
		if (is_array($vars))
			foreach ($vars as $name => $value)
				$tbs->MergeBlock($name, $value);
		$tbs->Show(TBS_NOTHING);
		if ($condition===TBS_TEST_DEBUGMODE) exit($tbs->Source);
		if ($this->isAppropriate($tbs,$condition)) return $this->assertEqual($tbs->Source, file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}
	
	/**
	 * return true if the feature is supposed to fail for the current TBS version .
	 * @param object $tbs       the current version of TBS
	 * @param string $condition false, or an expression about TBS or PHP version. Example: 'TBS<=3.5.1' or 'PHP>3.0.0'
	 * @return boolean          True if it's a bug for the current TBS version
	 */
	function isAppropriate(&$tbs, $condition) {
		if ($condition===false) {
			return true;
		} else {
			$what = strtoupper(substr($condition, 0,3)); // TBS or PHP expected
			$currVersion = ($what=='PHP') ? PHP_VERSION : $tbs->Version; 
			$x = $condition[4];
			$l = ( ($x=='=') || ($x=='>') ) ? 2 : 1;
			$ope = substr($condition, 3, $l);
			$compVersion = substr($condition, 3+$l);
			// exit("what=$what , currVersion=$currVersion , ope=$ope , compVersion=$compVersion"); // debug
			return version_compare($currVersion,$compVersion,$ope);
		}
	}
	
}

?>