<?php

class QuoteTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Quote Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testEmptyValue() {
		
		// with an emptystring
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='';block=b]</b>", array('a'=>''),  "", "test empty #1 (empty string)");
		$this->dumpLastSource(); // debug
		//$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='';block=b]</b>", array('a'=>''),  "<b></b>", "test empty #2 (empty string)"); // bug
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='';block=b]</b>", array('a'=>''),  "", "test empty #3 (empty string)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='';block=b]</b>", array('a'=>''),  "<b></b>", "test empty #4 (empty string)");

		// with 'null' value
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='';block=b]</b>", array('a'=>null),  "", "test empty #5 (null value)");
		//$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='';block=b]</b>", array('a'=>null),  "<b></b>", "test empty #6 (null value)"); // bug
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='';block=b]</b>", array('a'=>null),  "", "test empty #7 (null value)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='';block=b]</b>", array('a'=>null),  "<b></b>", "test empty #8 (null value)");

		// with 'false' value
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='';block=b]</b>", array('a'=>false),  "", "test empty #9 (false value)");
		//$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='';block=b]</b>", array('a'=>false),  "<b></b>", "test empty #10 (false value)"); // bug
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='';block=b]</b>", array('a'=>false),  "", "test empty #11 (false value)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='';block=b]</b>", array('a'=>false),  "<b></b>", "test empty #12 (false value)");

		// vicious: with an empty array !
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='';block=b]</b>", array('a'=>array()),  "<b></b>", "test empty #13 (array value)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='';block=b]</b>", array('a'=>array()),  "", "test empty #14 (array value)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='';block=b]</b>", array('a'=>array()),  "<b></b>", "test empty #15 (array value)");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='';block=b]</b>", array('a'=>array()),  "", "test empty #16 (array value)");
		
	}

	function testNotEmptyString() {
		
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='a';block=b]</b>", array('a'=>'a'),  "", "test quote #1");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='a';block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #2");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='a';block=b]</b>", array('a'=>'a'),  "", "test quote #3");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='a';block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #4");

		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!=a;block=b]</b>", array('a'=>'a'),  "", "test quote #5");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]=a;block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #6");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!=a;block=b]</b>", array('a'=>'a'),  "", "test quote #7");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'=a;block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #8");

		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!='';block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #9");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]='';block=b]</b>", array('a'=>'a'),  "", "test quote #10");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!='';block=b]</b>", array('a'=>'a'),  "<b></b>", "test quote #11");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'='';block=b]</b>", array('a'=>'a'),  "", "test quote #12");

		// Quotes one the value, and +/- operators
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'+-'3.5';block=b]</b>", array('a'=>'3.6'),  "<b></b>", "test quotes (any side), and +/- operator");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]+-3.5;block=b]</b>",   array('a'=>'3.6'),  "<b></b>", "test no quotes (any side), and +/- operator");
		
	}

	function testBugs() {
		
		// vicious: with an ';' as value
		$this->assertEqualMergeFieldStrings("<b>[onshow;when [a]!=';';block=b]</b>", array('a'=>';'),  "", "test bug #1");
		// $this->assertEqualMergeFieldStrings("<b>[onshow;when [a]=';';block=b]</b>", array('a'=>';'),  "<b></b>", "test bug #2"); // bug
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!=';';block=b]</b>", array('a'=>';'),  "", "test bug #3");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'=';';block=b]</b>", array('a'=>';'),  "<b></b>", "test bug #4");
		
		// vicious: with a simple quote as value
		//$this->assertEqualMergeFieldStrings("<b>[onshow;when [a;htmlconv=esc]!='''';block=b]</b>", array('a'=>'\''),  "", "test bug #5");
		//$this->assertEqualMergeFieldStrings("<b>[onshow;when [a;htmlconv=esc]='''';block=b]</b>", array('a'=>'\''),  "<b></b>", "test bug #6");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a;htmlconv=esc]'!='''';block=b]</b>", array('a'=>'\''),  "", "test bug #7");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a;htmlconv=esc]'='''';block=b]</b>", array('a'=>'\''),  "<b></b>", "test bug #8");
		
		// vicious: with an ']' as value
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'!=']';block=b]</b>", array('a'=>']'),  "", "test bug #11");
		$this->assertEqualMergeFieldStrings("<b>[onshow;when '[a]'=']';block=b]</b>", array('a'=>']'),  "<b></b>", "test bug #12");
		
	}
		
}

?>