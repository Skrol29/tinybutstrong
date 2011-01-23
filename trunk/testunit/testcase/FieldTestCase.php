<?php

class FieldTestCase extends TBSUnitTestCase {

	var $empty = '';
	var $test = 'toto';

	function FieldTestCase() {
		$this->UnitTestCase('Basic Merge Field Unit Tests');
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
		$this->assertErrorMergeFieldString("<b>[a.tEst]</b>", array('a'=>$this), "merge field sensitive case #5");
		$this->assertErrorMergeFieldString("<b>[a.TEST]</b>", array('a'=>$this), "merge field sensitive case #6");
	}

	function testUserFunction() {
		// $this->assertEqualMergeFieldStrings("<b>[a]</b>", array(), "<b>[a]</b>", "merge field default parameters #1a");
		// $this->tbs->MergeField('a', 'strToUpper', TRUE);
		// $this->assertEqualMergeString("<b>A</b>", "merge field default parameters #1b");
		// $this->dumpLastSource();
		// $this->dump($this->tbs);
	}

	function testBugs() {
		// merge bad array value syntax sould at least print a warning, except if 'noerr' parameter is used
		// $this->assertErrorMergeFieldString("<b>[a]</b>", array('a'=>array()), "merge array value with bad syntax #1"); // should display an error
		// $this->assertEqualMergeFieldStrings("<b>[a;noerr]</b>", array('a'=>array()), "<b></b>", "merge array value with bad syntax #2"); // should not display an error and skip field content ?

		// should work too if remplacing array value by object value !
		// $this->assertErrorMergeFieldString("<b>[a]</b>", array('a'=>$this), "merge object value with bad syntax #1"); // should display an error
		// $this->assertEqualMergeFieldStrings("<b>[a;noerr]</b>", array('a'=>$this), "<b></b>", "merge object value with bad syntax #2"); // should not display an error and skip field content ?
	}
}

?>