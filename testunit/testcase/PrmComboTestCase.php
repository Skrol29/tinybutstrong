<?php

class PrmComboTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Parameter combo Unit Tests');
	}

	function setUp() {
		// Reset global combo list before each test to avoid pollution
		$GLOBALS['_TBS_PrmCombo'] = array();
	}

	function tearDown() {
		$GLOBALS['_TBS_PrmCombo'] = array();
	}

	function testKnownCombo() {
		// A registered combo's parameters are applied to the field.
		// Here 'safe' adds noerr so a missing variable produces no error.
		$this->newInstance = false;
		$this->tbs = new clsTinyButStrong;
		$this->tbs->SetOption('prm_combo', array('safe' => array('noerr' => '')));

		$this->assertEqualMergeFieldStrings('[onshow.nonexistent;combo=safe]', array(), '', 'known combo #1 - output is empty');
		$this->assertNoTbsError('known combo #1 - no TBS error');
	}

	function testUnknownComboRaisesTbsError() {
		// Referencing an undefined combo must raise a TBS error instead of a PHP fatal error.
		// Before the fix, this caused: "Fatal error: Using $this when not in object context"
		// because meth_Misc_ApplyPrmCombo is static but called $this->meth_Misc_Alert().
		$this->tbs = new clsTinyButStrong;
		$this->tbs->NoErr = true;
		$this->tbs->Source = '[onshow.msg;combo=undefined_combo]';
		$this->tbs->VarRef['msg'] = 'hello';
		$this->tbs->Show(TBS_NOTHING);

		$this->assertTrue($this->tbs->ErrCount > 0, 'unknown combo - TBS error is raised');
		$this->assertEqual($this->tbs->Source, 'hello', 'unknown combo - field value still rendered');
	}

}
