<?php

class FrmTestCase extends TBSUnitTestCase {

	function FrmTestCase() {
		$this->UnitTestCase('Frm Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testNumericFormats() {

		// decimal and thousand separators
		$this->assertEqualMergeFieldStrings("{[a;frm=0.]}", array('a'=>3128.50),  "{3129}", "test round limit #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.]}", array('a'=>3128.49),  "{3128}", "test round limit #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00]}", array('a'=>3128.495),  "{3128.50}", "test decimals alone");
		$this->assertEqualMergeFieldStrings("{[a;frm=0 000.00]}", array('a'=>3128.495),  "{3 128.50}", "test thousand and decimal #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=0 000,00]}", array('a'=>3128.495),  "{3 128,50}", "test thousand and decimal #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0,000.00]}", array('a'=>3128.495),  "{3,128.50}", "test thousand and decimal #3");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.000 00]}", array('a'=>3128.495),  "{3.128 50}", "test thousand and decimal #4 wired separators");

		// pourcents
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00%]}", array('a'=>0.495),  "{49.50%}", "test pourcent standard");
		$this->assertEqualMergeFieldStrings("{[a;frm=0.00 %]}", array('a'=>0.495),  "{49.50 %}", "test pourcent #1");
		$this->assertEqualMergeFieldStrings("{[a;frm=% 0.00]}", array('a'=>0.495),  "{% 49.50}", "test pourcent #2");
		$this->assertEqualMergeFieldStrings("{[a;frm=0,000.00%]}", array('a'=>3128.495),  "{312,849.50%}", "test pourcent with thousand separator");

		}
		
}

?>