<?php

class MiscTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Miscelaneous Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testInstanciation() {

		// BUG: TBS support closing char with only 1 char

		// Old instanciation syntax
		// ------------------------
		
		// change delimiter, [val] is also tested, protection is also tested
		$this->tbs = new clsTinyButStrong('{{,}');
		$this->newInstance = false;		
		$this->assertEqualMergeFieldStrings("{{a}|{{a;if {{val}=0;then 29}|{{b}", array('a'=>0, 'b'=>'{{'),  "0|29|&#123;{", "OLD delimiters");
		//$this->dumpLastSource();
		
		// var prefix
		$this->tbs = new clsTinyButStrong('', 'vvv');
		$this->assertEqual($this->tbs->VarPrefix, 'vvv', "OLD var prefix");

		// fct prefix
		$this->tbs = new clsTinyButStrong('', '', 'fff');
		$this->assertEqual($this->tbs->FctPrefix, 'fff', "OLD fct prefix");
		
		// New instanciation syntax
		// ------------------------
		
		$Options1 = array('chr_open'=>'{{', 'chr_close'=>'}');
		
		$this->tbs = new clsTinyButStrong($Options1);
		$this->newInstance = false;		
		$this->assertEqualMergeFieldStrings("{{a}|{{a;if {{val}=0;then 29}|{{b}", array('a'=>0, 'b'=>'{{'),  "0|29|&#123;{", "NEW delimiters");
		$this->assertEqual($this->tbs->VarPrefix, '', "NEW var prefix");
		$this->assertEqual($this->tbs->FctPrefix, '', "NEW fct prefix");
	
		$Options2 = array('var_prefix'=>'vvvv', 'fct_prefix'=>'ffff');
		$this->tbs->SetOption($Options2);
		$this->assertEqual($this->tbs->VarPrefix, 'vvvv', "NEW var prefix");
		$this->assertEqual($this->tbs->FctPrefix, 'ffff', "NEW fct prefix");
	
	}


	/**
	 * Important since the changes concerning $GLOBALS since PHP 8.1
	 */
	function testGlobalVariables() {
		
		// Using the $GLOBALS variable
		$GLOBALS['xxx'] = 'XXX';
		$this->assertEqualMergeFieldStrings("<div>[onshow.xxx]</div>", array(), "<div>XXX</div>", "test Global #1");
		
		// Using the « global » instruction
		global $yyy;
		$yyy = 'YYY';
		$this->assertEqualMergeFieldStrings("<div>[onshow.yyy]</div>", array(), "<div>YYY</div>", "test Global #2");
		
		// Using a separated VarRef
		$this->newInstance = false;
		$this->tbs = new clsTinyButStrong;
		$this->tbs->ResetVarRef(false); // VarRef is now a new empty array
		$this->tbs->VarRef['zzz'] = 'ZZZ';
		$this->assertEqualMergeFieldStrings("<div>[onshow.zzz]</div>", array(), "<div>ZZZ</div>", "test Global #3");
		
	}


}
