<?php

// Plugins
class clsTbsTestOnDataPlugIn {
	public $TBS;
	public $Version = '1.00';
	function OnInstall() {
		return array('OnData');
	}	
	function OnData($BlockName,&$CurrRec,$RecNum,&$TBS) {
		$CurrRec['name'] .= "XX"; // simply add a suffix
	}
}


class PluginsTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Miscelaneous Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testOnData() {


		
		$this->tbs = new clsTinyButStrong;
		$this->tbs->PlugIn(TBS_INSTALL, 'clsTbsTestOnDataPlugIn');

		$data = array(
			['name'  => "Peter"],
			['name'  => "Paul"],
			['name'  => "Max"]
		);

		$this->newInstance = false;		
		$this->assertEqualMergeBlockString("<div>[b.name;block=div]</div>", array('b'=>$data), '<div>PeterXX</div><div>PaulXX</div><div>MaxXX</div>', "test block with Plugin OnData");
		
		//$this->dumpLastSource();
	
	}



}
