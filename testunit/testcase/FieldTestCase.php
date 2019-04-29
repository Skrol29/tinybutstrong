<?php

function FieldTestCaseUserFunction($param1, $param2) {
	return "<param1>".serialize($param1)."</param1><param2>".serialize($param2)."</param2>";
}

class FieldTestCase extends TBSUnitTestCase {

	var $empty = '';
	var $test = 'toto';

	function __construct() {
		$this->UnitTestCase('Basic MergeField Unit Tests');
	}

	function FieldTestCaseUserMethod($param1) {
		return "<param1>".serialize($param1)."</param1>";
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testEmptyValue() {
		// test existing fields with empty value
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array(), "<b>[a]</b>", "merge empty field #1");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>''), "<b></b>", "merge empty field #2");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>FALSE), "<b></b>", "merge empty field #3");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>null), "<b></b>", "merge empty field #4");
		$this->assertEqualMergeFieldStrings("<b>[a.b]</b>", array('a'=>array('b'=>'')), "<b></b>", "merge empty field #5");
		$this->assertEqualMergeFieldStrings("<b>[a.empty]</b>", array('a'=>$this), "<b></b>", "merge empty field #6");

		// test non existing fields with empty value
		$this->assertErrorMergeFieldString("<b>[a.c]</b>", array('a'=>array('b'=>'')), "merge non existing field #1");
		$this->assertErrorMergeFieldString("<b>[a.c]</b>", array('a'=>array($this)), "merge non existing field #2");
		$this->assertErrorMergeFieldString("<b>[a.c]</b>", array('a'=>''), "merge non existing field #3");

		// same as previous but with 'noerr' parameter
		$this->assertEqualMergeFieldStrings("<b>[a.c;noerr]</b>", array('a'=>array('b'=>'')), "<b></b>", "merge non existing field with noerr #1a");
		$this->assertNoTbsError("merge non existing field with noerr #1b");
		$this->assertEqualMergeFieldStrings("<b>[a.c;noerr]</b>", array('a'=>array($this)), "<b></b>", "merge non existing field with noerr #2b");
		$this->assertNoTbsError("merge non existing field with noerr #2b");
		$this->assertEqualMergeFieldStrings("<b>[a.c;noerr]</b>", array('a'=>''), "<b></b>", "merge non existing field with noerr #3c");
		$this->assertNoTbsError("merge non existing field with noerr #3b");
	}

	function testNonEmptyValue() {
		// test existing fields with non empty value
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>'['), "<b>&#91;</b>", "merge non empty field #1");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>'a'), "<b>a</b>", "merge non empty field #2");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>TRUE), "<b>1</b>", "merge non empty field #3");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>'<'), "<b>&lt;</b>", "merge non empty field #4");
		$this->assertEqualMergeFieldStrings("<b>[a.b]</b>", array('a'=>array('b'=>'c')), "<b>c</b>", "merge non empty field #5");
		$this->assertEqualMergeFieldStrings("<b>[a.test]</b>", array('a'=>$this), "<b>toto</b>", "merge non empty field #6");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>'&'), "<b>&amp;</b>", "merge non empty field #7");
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('a'=>'noël à la crême où ça'), "<b>noël à la crême où ça</b>", "merge non empty field #8");

		// test existing fields merge order
		$this->assertEqualMergeFieldStrings("<b>[[a]]</b>", array('a'=>'c', 'b'=>'a', 'c'=>'b'), "<b>b</b>", "merge field order #1");
		$this->assertEqualMergeFieldStrings("<b>[[c]]</b>", array('a'=>'c', 'b'=>'a', 'c'=>'b'), "<b>[b]</b>", "merge field order #2");
		$this->assertEqualMergeFieldStrings("<b>[[[a]]]</b>", array('a'=>'c', 'b'=>'a', 'c'=>'b'), "<b>[b]</b>", "merge field order #3");
		$this->assertEqualMergeFieldStrings("<b>[[[b]]]</b>", array('a'=>'c', 'b'=>'a', 'c'=>'b'), "<b>[[a]]</b>", "merge field order #4");
		$this->assertEqualMergeFieldStrings("<b>[[[[a]]]]</b>", array('a'=>'b', 'b'=>'c', 'c'=>'d', 'd'=>'e'), "<b>e</b>", "merge field order #5");

		// test existing fields sensitive case
		$this->assertEqualMergeFieldStrings("<b>[a]</b>", array('A'=>'b'), "<b>[a]</b>", "merge field sensitive case #1");
		$this->assertEqualMergeFieldStrings("<b>[A]</b>", array('a'=>'b'), "<b>[A]</b>", "merge field sensitive case #2");
		$this->assertEqualMergeFieldStrings("<b>[A]</b>", array('a'=>'b', 'A'=>'c'), "<b>c</b>", "merge field sensitive case #3");
		$this->assertEqualMergeFieldStrings("<b>[Abc][ABC][ABc][AbC][abc][aBc]</b>", array('abc'=>1, 'Abc'=>2, 'aBc'=>3, 'abC'=>4, 'ABc'=>5, 'AbC'=>6, 'aBC'=>7, 'ABC'=>8), "<b>285613</b>", "merge field sensitive case #4");
		if ($this->atLeastPhpVersion('5.0')) {
			$this->assertErrorMergeFieldString("<b>[a.tEst]</b>", array('a'=>$this), "merge field sensitive case #5");
			$this->assertErrorMergeFieldString("<b>[a.TEST]</b>", array('a'=>$this), "merge field sensitive case #6");
		}
	}

	function testUserFunction() {
		// call user method with no subname
		$this->assertEqualMergeFieldStrings("<b>[a;noerr;htmlconv=no;test=toto]</b>", array(), "<b>[a;noerr;htmlconv=no;test=toto]</b>", "merge field by calling user function #1a");
		$this->tbs->MergeField('a', 'FieldTestCaseUserFunction', TRUE);
		$this->assertEqualMergeString('<b><param1>s:0:"";</param1><param2>a:3:{s:5:"noerr";b:1;s:8:"htmlconv";s:2:"no";s:4:"test";s:4:"toto";}</param2></b>', "merge field by calling user function #1b");

		// same using default parameters
		$this->assertEqualMergeFieldStrings("<b>[a;noerr;htmlconv=no;test=toto]</b>", array(), "<b>[a;noerr;htmlconv=no;test=toto]</b>", "merge field by calling user function #2a");
		$this->tbs->MergeField('a', 'FieldTestCaseUserFunction', TRUE, array('noerr'=>'hack me !', 'titi'=>';', 'ondata'=>'die'));
		$this->assertEqualMergeString('<b><param1>s:0:"";</param1><param2>a:5:{s:5:"noerr";b:1;s:4:"titi";s:1:";";s:6:"ondata";s:3:"die";s:8:"htmlconv";s:2:"no";s:4:"test";s:4:"toto";}</param2></b>', "merge field by calling user function #2b");

		// call user method with a subname
		$this->assertEqualMergeFieldStrings("<b>[a.test;test=toto]</b>", array(), "<b>[a.test;test=toto]</b>", "merge field by calling user function #3a");
		$this->tbs->MergeField('a', 'array_key_exists', TRUE);
		$this->assertEqualMergeString('<b>1</b>', "merge field by calling user function #3b");

		// call user method with a subname and default parameters
		$this->assertEqualMergeFieldStrings("<b>[a.test]</b>", array(), "<b>[a.test]</b>", "merge field by calling user function #4a");
		$this->tbs->MergeField('a', 'array_key_exists', TRUE, array('test'=>null));
		$this->assertEqualMergeString('<b>1</b>', "merge field by calling user function #4b");
	}

	function testChainCallFunction() {
		$data = array('a'=>array('aa'=>array('aaa'=>array('aaaa'=>array('aaaaa'=>'1'))), 'ab'=>array('aba'=>'2', 'abb'=>array('abba'=>'3')), 'ac'=>'4', 'ad'=>$this), 'b'=>'5');
		$this->assertEqualMergeFieldStrings("<b>[b]</b>", $data, "<b>5</b>", "merge field with chain call #1");
		$this->assertEqualMergeFieldStrings("<b>[a.ac]</b>", $data, "<b>4</b>", "merge field with chain call #2");
		$this->assertEqualMergeFieldStrings("<b>[a.ab.aba]</b>", $data, "<b>2</b>", "merge field with chain call #3");
		$this->assertEqualMergeFieldStrings("<b>[a.ab.abb.abba]</b>", $data, "<b>3</b>", "merge field with chain call #4");
		$this->assertEqualMergeFieldStrings("<b>[a.aa.aaa.aaaa.aaaaa]</b>", $data, "<b>1</b>", "merge field with chain call #5");
		$this->assertEqualMergeFieldStrings("<b>[a.ad.test]</b>", $data, "<b>toto</b>", "merge field with chain call #6");
		$this->assertEqualMergeFieldStrings("<b>[a.ad._reporter._character_set]</b>", $data, "<b>".$this->_reporter->_character_set."</b>", "merge field with chain call #7");
	}

	function testVarRef() {
		// test existing fields with non empty value
		global $zzzz;
		$zzzz =  'ok';
		$this->assertEqualMergeFieldStrings("<b>[a;if '[var.zzzz]'='ok';then 1;else 2]</b>", array('a'=>'aaa'), "<b>1</b>", "merge global var #1");
		
		if (!$this->atLeastTBSVersion('3.8')) return;
		
		$VarRef = array('zzzz'=>'new');
		$this->tbs->VarRef =& $VarRef;
		
		$this->newInstance = false;
		$this->assertEqualMergeFieldStrings("<b>[a;if '[var.zzzz]'='new';then 1;else 2]</b>", array('a'=>'aaa'), "<b>1</b>", "merge VarRef #1");
		
	}
	
	function testBugs() {
		// merge bad array value syntax sould at least print a warning, except if 'noerr' parameter is used
		// $this->assertErrorMergeFieldString("<b>[a]</b>", array('a'=>array()), "merge array value with bad syntax #1"); // should display an error
		// $this->assertEqualMergeFieldStrings("<b>[a;noerr]</b>", array('a'=>array()), "<b></b>", "merge array value with bad syntax #2"); // should not display an error and skip field content ?

		// should work too if remplacing array value by object value !
		// $this->assertErrorMergeFieldString("<b>[a]</b>", array('a'=>$this), "merge object value with bad syntax #1"); // should display an error
		// $this->assertEqualMergeFieldStrings("<b>[a;noerr]</b>", array('a'=>$this), "<b></b>", "merge object value with bad syntax #2"); // should not display an error and skip field content ?

		// use object method should work !
		// $this->assertEqualMergeFieldStrings("<b>[a.test]</b>", array(), "<b>[a.test]</b>", "merge field with object method #1");
		// $this->tbs->MergeField('a', array(&$this, 'FieldTestCaseUserMethod'), TRUE);
		// $this->assertEqualMergeString('<b><param1>s:0:"test";</param1></b>', "merge field with object method#2");
	}
}

?>