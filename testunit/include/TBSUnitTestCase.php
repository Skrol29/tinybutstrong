<?php

// override unit test case  class (unit_tester.php) to simplify tinyButStrong test cases
class TBSUnitTestCase extends UnitTestCase {

	/**
	 * Last instance of 'clsTinyButStrong' class.
	 */
	var $tbs;
	var $newInstance = true;

	/**
	 * Dump last merge result.
	 * @return string
	 */
	function dumpLastSource() {
		if (!is_null($this->tbs))
			$this->dump($this->tbs->Source);
	}

	/**
	 * Assert last merge result produce no error.
	 * @param string $message        message to display (optional)
	 */
	function assertNoTbsError($message='%s') {
		if (!is_null($this->tbs))
			$this->assertEqual($this->tbs->ErrCount, 0, $message);
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
	 * Instanciate TBS class with one function. Launch plugins and merge 'onload' directives.
	 * @param string $source         source of template
	 */
	function createTBSInstance($source) {
		if ($this->newInstance) {
			$this->tbs = new clsTinyButStrong;
		} else {
			$this->newInstance = true;
		}
		$this->tbs->Source = $source;
		$this->tbs->LoadTemplate(null);
	}

	/**
	 * Return TBS render result as string.
	 * @return string
	 */
	function getTBSRender() {
		if (is_null($this->tbs)) return FALSE;
		$this->tbs->Show(TBS_NOTHING);
		return $this->tbs->Source;
	}

	/**
	 * Returns directory of HTML files to compare.
	 */
	function getTemplateDir() {
		return dirname(dirname(__FILE__)).'/template/';
	}
	
	/**
	 * Test TBS merge result with expected result.
	 * @param string $expected       expected merge result
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeString($expected, $message='%s') {
        $result = $this->getTBSRender(); 
		return $this->assertEqual($result, $expected, $message);
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
		$this->createTBSInstance($source);
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeField($name, $value);
			}
		}
		return $this->assertEqualMergeString($result, $message);
	}

	/**
	 * Test TBS class errors with one function.
	 * @param string $source         source of template
	 * @param array $vars            associative array of name/value to pass to MergeField
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertErrorMergeFieldString($source, $vars, $message='%s') {
		$this->createTBSInstance($source);
		$this->tbs->NoErr = TRUE;
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeField($name, $value);
			}
		}
		$this->getTBSRender();
		return $this->assertTrue($this->tbs->ErrCount > 0, $message);
	}

	/**
	 * Test TBS class with one function. Work only withe 'clear' and array data.
	 * @param string $source         source of template
	 * @param array  $vars           associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockString($source, $vars, $result, $message='%s') {
		$this->createTBSInstance($source);
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, $value);
			}
		}
		return $this->assertEqualMergeString($result, $message);
	}

	/**
	 * Test TBS class errors with one function.
	 * @param string $source         source of template
	 * @param array  $vars           associative array of name/value to pass to MergeBlock
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertErrorMergeBlockString($source, $vars, $message='%s') {
		$this->createTBSInstance($source);
		$this->tbs->NoErr = TRUE;
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, $value);
			}
		}
		$this->getTBSRender();
		return $this->assertTrue($this->tbs->ErrCount > 0, $message);
	}

	/**
	 * Test TBS class with one function. Work only withe 'clear' and array data.
	 * @param string $source         source of template
	 * @param array  $vars           associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockResult($source, $vars, $result, $message='%s') {
		$this->createTBSInstance($source);
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$res = $this->tbs->MergeBlock($name, $value);
			}
		}
		return $this->assertIdentical($result, $res , $message);
	}
	
	/**
	 * Test TBS class with one function. Use 'num' parameter as second MergeBlock parameter.
	 * @param string $source         source of template
	 * @param array  $vars           associative array of name/value to pass to MergeBlock
	 * @param string $result         merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeNumBlockStrings($source, $vars, $result, $message='%s') {
		$this->createTBSInstance($source);
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, 'num', $value);
			}
		}
		return $this->assertEqualMergeString($result, $message);
	}
	
	function assertEqualMergeArrayBlockStrings($source, $vars, $result, $message='%s') {
		$this->createTBSInstance($source);
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, 'array', $value);
			}
		}
		return $this->assertEqualMergeString($result, $message);
	}

	function assertErrorMergeArrayBlockStrings($source, $vars, $message='%s') {
		$this->createTBSInstance($source);
		$this->tbs->NoErr = TRUE;
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, 'array', $value);
			}
		}
		$this->getTBSRender();
		return $this->assertTrue($this->tbs->ErrCount > 0, $message);
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
		$this->createTBSInstance(file_get_contents($this->getTemplateDir().$sourceFilename));
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeField($name, $value);
			}
		}
		return $this->assertEqualMergeString(file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}

	/**
	 * Test TBS class with one function.
	 * @param string $sourceFilename fine name of template source
	 * @param array  $vars           associative array of name/value to pass to MergeBlock
	 * @param string $resultFilename file name of merge result to compare
	 * @param string $message        message to display (optional)
	 * @return boolean               True on pass
	 */
	function assertEqualMergeBlockFiles($sourceFilename, $vars, $resultFilename, $message='%s') {
		$this->createTBSInstance(file_get_contents($this->getTemplateDir().$sourceFilename));
		if (is_array($vars)) {
			foreach ($vars as $name => $value) {
				$this->tbs->MergeBlock($name, $value);
			}
		}
		return $this->assertEqualMergeString(file_get_contents($this->getTemplateDir().$resultFilename), $message);
	}
}

?>