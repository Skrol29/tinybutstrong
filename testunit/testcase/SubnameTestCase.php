<?php

/**
 * Class for tests : without __call()
 */
class ObjTest1 {
	
	public $prop1 = 'test-p1-ok';
	public $prop2 = array(
		'n3' => 'test-p2-ok',
	);
	
	public function meth1() {
		return 'test-m1-ok';
	}

	public function meth2() {
		return array(
			'n2' => 'test-m2-ok',
		);
	}

	public function meth3($x) {
		return 'test-m3-' . $x;
	}
	
	public function meth4($x, $y) {
		return 'test-m4-' . $x . '-' . $y;
	}

	// return recordset to merge
	public function methM() {
		return array(
			array('name'=> 'X'),
			array('name'=> 'Y'),
			array('name'=> 'Z'),
		);
	}
	
	// used for onformat
	public function methOnFormat1($FieldName, &$CurrVal) {
		$CurrVal = 'test-' . $CurrVal . '-ok';
	}
	
	public function donneObj1() {
		$obj = new ObjTest1;
		return $obj;
	}
	
}

/**
 * Class for tests : with __call()
 */
class ObjTest2 {
	
	public function methA() {
		return 'test-ma-ok';
	}

	public function donneObj2() {
		$obj = new ObjTest2;
		return $obj;
	}
	
	// Overloaded methods
	function __call($name, $arguments) {
		
		if ($name == 'methB') {
			return 'test-mb-ok';
		} elseif ($name == 'methD') {
			return array(
				array('name'=> 'A'),
				array('name'=> 'B'),
				array('name'=> 'C'),
			);
		} elseif ($name == 'methOnFormat2') {
			$CurrVal =& $arguments[1];
			$CurrVal = 'test-' . $CurrVal . '-ok2';
		} else {
			return 'test-mb-ERR';
		}
		
	}
	
	function __isset($name) {
		if ($name == 'propB') {
			return true;
		} else {
			return false;
		}
	}
	
	// Overloaded propeties
	function __get($name) {
		if ($name == 'propB') {
			return 'test-pb-ok';
		} else {
			return 'test-pb-ERR';
		}
	}
	
}

class SubnameTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Subname Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function skip() {
		// run tests only if tbs version >= 3.5.0
		//$this->skipIfNotAtLeastTBSVersion('3.5.0', 'skip att option unit tests because it was not implemented in this TinyButStrong version');
	}

	function testFields() {
		
		// ------------
		// prepare data
		// ------------
		
		$obj1 = new ObjTest1;
		$obj2 = new ObjTest2; // with __call() and __get()
		$arr  = array(
			'lev1' => 'test-a1-ok', 
			'lev2' => array(
				'x1' => 'test-a2-ok',
			), 
		);
		
		$test_obj1 = array('obj1' => $obj1);
		$test_obj2 = array('obj2' => $obj2);
		$test_arr  = array('arr' => $arr);
		
		// -----
		// Tests
		// -----
		
		// array
		$this->assertEqualMergeFieldStrings("<p>[arr.lev1]</p>", $test_arr, "<p>test-a1-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[arr.lev2.x1]</p>", $test_arr, "<p>test-a2-ok</p>", "test subname #" . __LINE__);
		
		// object
		$this->assertEqualMergeFieldStrings("<p>[obj1.prop1]</p>",        $test_obj1, "<p>test-p1-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj1.prop2.n3]</p>",     $test_obj1, "<p>test-p2-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth1]</p>",        $test_obj1, "<p>test-m1-ok</p>", "test subname#" . __LINE__); // Method pointed as a property (deprecated but supported)
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth1()]</p>",      $test_obj1, "<p>test-m1-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth2.n2]</p>",     $test_obj1, "<p>test-m2-ok</p>", "test subname #" . __LINE__); // Method pointed as a property (deprecated but supported)
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth2().n2]</p>",   $test_obj1, "<p>test-m2-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth3(YO)]</p>",    $test_obj1, "<p>test-m3-YO</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj1.meth4(DO,RE)]</p>", $test_obj1, "<p>test-m4-DO-RE</p>", "test subname #" . __LINE__);
		
		// Error: item meth5' is neither a method nor a property in the class 'ObjTest'
		$this->assertErrorMergeFieldString("<p>[obj1.meth5]</p>",         $test_obj1, "test subname #" . __LINE__); // Method pointed as a property (deprecated but supported)
		$this->assertErrorMergeFieldString("<p>[obj1.meth5()]</p>",       $test_obj1, "test subname #" . __LINE__);
		
		// method methA() exists
		$this->assertEqualMergeFieldStrings("<p>[obj2.methA]</p>",        $test_obj2, "<p>test-ma-ok</p>", "test subname #" . __LINE__);
		$this->assertEqualMergeFieldStrings("<p>[obj2.methA()]</p>",      $test_obj2, "<p>test-ma-ok</p>", "test subname #" . __LINE__);

		// method methB() is overloaded
		$this->assertErrorMergeFieldString("<p>[obj2.methB]</p>",         $test_obj2, "test subname #" . __LINE__); // Method pointed as a property (deprecated but supported)

		// property propB() is overloaded
		$this->assertEqualMergeFieldStrings("<p>[obj2.propB]</p>",         $test_obj2, "<p>test-pb-ok</p>", "test subname #" . __LINE__);
		
		if ($this->atLeastTBSVersion('3.11')) {
			// Supports classes with magic function __call()
			$this->assertEqualMergeFieldStrings("<p>[obj2.methB()]</p>",  $test_obj2, "<p>test-mb-ok</p>", "test subname #" . __LINE__);
		} else {
			$this->assertErrorMergeFieldString("<p>[obj2.methB()]</p>",   $test_obj2, "test subname #" . __LINE__);
		}
		
	}

	function testMergeBlockArray() {

		// --------------------
		// Loop for test series
		// --------------------
		
		$prefix_lst = array(
			'var_ref_1',   // item of VarRef
			'~obj_ref_1',  // item of ObjectRef
		); 
		
		foreach ($prefix_lst as $prefix) {
			
			$this->_init_tbs_with_refs();
			$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj1]->methM()"), "<p><i>X</i><i>Y</i><i>Z</i></p>", "test subname {$prefix} #" . __LINE__);

			$this->_init_tbs_with_refs();
			$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj1]->methM"), "<p><i>X</i><i>Y</i><i>Z</i></p>", "test subname {$prefix} #" . __LINE__);
			
			// When method or perperty is not found, there is no error but data is empty (!).
			$this->_init_tbs_with_refs();
			$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj1]->methN"), "<p></p>", "test subname {$prefix} #" . __LINE__);

			$this->_init_tbs_with_refs();
			$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj1]->methN()"), "<p></p>", "test subname {$prefix} #" . __LINE__);
			
			// Method pointed as a property (deprecated but supported)
			$this->_init_tbs_with_refs();
			$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj2]->methD"), "<p></p>", "test subname {$prefix} #" . __LINE__);
			
			if ($this->atLeastTBSVersion('3.11')) {
				// Supports classes with magic function __call()
				$this->_init_tbs_with_refs();
				$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj2]->methD()"), "<p><i>A</i><i>B</i><i>C</i></p>", "test subname {$prefix} #" . __LINE__);
			} else {
				$this->_init_tbs_with_refs();
				$this->assertEqualMergeArrayBlockStrings("<p><i>[b1.name;block=i]</i></p>", array('b1' => "{$prefix}[item1][obj2]->methD()"), "<p></p>", "test subname {$prefix} #" . __LINE__);
			}
			
		}
		
	}

	function testUserFct() {
		
		$this->_init_tbs_with_refs();
		$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj1.methOnFormat1]</p>", array('f1' => 'yep'), "<p>test-yep-ok</p>", "test subname #" . __LINE__);
		//$this->dumpLastSource();

		// This test produces a PHP error, wich is normal but cannot be cleany catched by UnitTestCase :
		// Unexpected PHP error [call_user_func_array() expects parameter 1 to be a valid callback, class 'ObjTest1' does not have a method 'methOnFormat1()']
		//$this->_init_tbs_with_refs();
		//$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj1.methOnFormat1()]</p>", array('f1' => 'yep'), "<p>test-yep-ok</p>", "test subname #" . __LINE__);
		
		$this->_init_tbs_with_refs();
		$this->assertErrorMergeFieldString("<p>[f1;onformat=~obj_ref_1.item1.obj1.methOnFormatX]</p>", array('f1' => 'yep'), "test subname #" . __LINE__);

		// Method pointed as a property (deprecated but supported)
		$this->_init_tbs_with_refs();
		$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj1.donneObj1.methOnFormat1]</p>", array('f1' => 'yep'), "<p>test-yep-ok</p>", "test subname #" . __LINE__);

		$this->_init_tbs_with_refs();
		$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj1.donneObj1().methOnFormat1]</p>", array('f1' => 'yep'), "<p>test-yep-ok</p>", "test subname #" . __LINE__);
		
		if ($this->atLeastTBSVersion('3.11')) {

			// Supports classes with magic function __call()
			
			$this->_init_tbs_with_refs();
			$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj2.methOnFormat2]</p>", array('f1' => 'yep'), "<p>test-yep-ok2</p>", "test subname #" . __LINE__);

			$this->_init_tbs_with_refs();
			$this->assertEqualMergeFieldStrings("<p>[f1;onformat=~obj_ref_1.item1.obj2.donneObj2().methOnFormat2]</p>", array('f1' => 'yep'), "<p>test-yep-ok2</p>", "test subname #" . __LINE__);
			
		} else {
			$this->_init_tbs_with_refs();
			$this->assertErrorMergeFieldString("<p>[f1;onformat=~obj_ref_1.item1.obj2.methOnFormat2]</p>", array('f1' => 'yep'), "test subname #" . __LINE__);
		}
		
	}
	
	/**
	 * It is necessary to craete a new instance becaus otherwise error can be cumulated.
	 */
	private function _init_tbs_with_refs() {

		$obj1 = new ObjTest1;
		$obj2 = new ObjTest2;
		
		$this->tbs = new clsTinyButStrong;
		
		$this->tbs->ObjectRef = array();
		$this->tbs->ObjectRef['obj_ref_1'] = array(
			'item1' => array(
				'obj1' => $obj1,
				'obj2' => $obj2,
			),
		);
		
		$this->tbs->ResetVarRef(false); // VarRef is now a new empty array
		$this->tbs->VarRef['var_ref_1'] = array(
			'item1' => array(
				'obj1' => $obj1,
				'obj2' => $obj2,
			),
		);

		$this->newInstance = false;
	
	}

}
