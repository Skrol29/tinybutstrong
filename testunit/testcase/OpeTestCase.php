<?php

class OpeTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Ope Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testOpe() {

		// list
		$this->assertEqualMergeFieldStrings('{[a;ope=list]}', array('a'=>array('x','y','z')),  '{x,y,z}', 'test ope=list');
		$this->assertEqualMergeFieldStrings('{[a;ope=list;valsep=+]}', array('a'=>array('x','y','z')),  '{x+y+z}', 'test ope=list valsep=+');
		
		// debug
		$this->assertEqualMergeFieldStrings('{[a;ope=debug]}', array('a'=>3128.50),  '{(double) 3128.5}', 'test debug double');
		$this->assertEqualMergeFieldStrings('{[a;ope=debug]}', array('a'=>null),  '{(NULL) NULL}',        'test debug null');
		//$this->dumpLastSource(); // debug
		
	}

}

