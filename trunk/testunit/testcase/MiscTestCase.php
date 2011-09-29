<?php

class MiscTestCase extends TBSUnitTestCase {

	function MiscTestCase() {
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
		$this->tbs->SetOptions($Options2);
		$this->assertEqual($this->tbs->VarPrefix, 'vvvv', "NEW var prefix");
		$this->assertEqual($this->tbs->FctPrefix, 'ffff', "NEW fct prefix");
	
	}


}

?>