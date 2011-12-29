<?php

class StrconvTestCase extends TBSUnitTestCase {

	function StrconvTestCase() {
		$this->UnitTestCase('Strconv Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function doTestTag($tag) {

		$x = "<-\r\n-  ->'";
	
		// decimal and thousand separators
		$this->assertEqualMergeFieldStrings("{[a]}", array('a'=>$x),               "{&lt;-<br />\r\n-  -&gt;'}", "test ".$tag." default");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=yes]}", array('a'=>$x),   "{&lt;-<br />\r\n-  -&gt;'}", "test ".$tag."=yes");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=no]}", array('a'=>$x),    "{<-\r\n-  ->'}", "test ".$tag."=no");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=nobr]}", array('a'=>$x),  "{&lt;-\r\n-  -&gt;'}", "test ".$tag."=nobr");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=yes+nobr]}", array('a'=>$x),  "{&lt;-\r\n-  -&gt;'}", "test ".$tag."=yes+nobr");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=wsp]}", array('a'=>$x),   "{&lt;-<br />\r\n-&nbsp; -&gt;'}", "test ".$tag."=wsp");
		$this->assertEqualMergeFieldStrings("{[a;".$tag."=esc]}", array('a'=>$x),   "{<-\r\n-  ->''}", "test ".$tag."=esc");
		//$this->dumpLastSource(); // debug
	
	}
	
	function testStrconv() {

		if ($this->atLeastTBSVersion('1.8.0')) $this->doTestTag('strconv');
		
	}

	function testHtmlconv() {

		$this->doTestTag('htmlconv');
		
	}
	
}

?>