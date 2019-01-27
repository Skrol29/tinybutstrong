<?php

class SubTplTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Sub-Template Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testGetPart() {
	
		/*
		$blk = array();
		$blk[] = array('id'=>'AttCache x12', 'val'=>'effect1');
		$blk[] = array('id'=>'AttCache x13', 'val'=>'effect2');
		$blk[] = array('id'=>'AttCache x14', 'val'=>'effect3');
		$blk[] = array('id'=>'AttCache x15', 'val'=>'effect4');
		*/
		
		// basic test
		$this->assertEqualMergeBlockFiles('subtpl_test1_main.html', array(), 'subtpl_test1_result.html', "sub-template #1");

		// getpart test
		$this->assertEqualMergeBlockFiles('subtpl_test2_main.html', array(), 'subtpl_test2_result.html', "sub-template #2");

		// store test
		$this->assertEqualMergeBlockFiles('subtpl_test3_main.html', array(), 'subtpl_test3_result.html', "sub-template #3");
		
	}

}

