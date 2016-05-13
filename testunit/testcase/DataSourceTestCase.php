<?php

class DataSourceTestCase extends TBSUnitTestCase {

	function DataSourceTestCase() {
		$this->UnitTestCase('DataSource Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testSortBy() {
		$data = array(
			0 => array('a' => 1, 'b' => '2', 'c' => 'ab100', 'd' => '1'),
			1 => array('a' => 2, 'b' => '1', 'c' => 'ab20',  'd' => '2'),
			2 => array('a' => 0, 'b' => 'a', 'c' => 'ab35',  'd' => '1'),
			3 => array('a' => 5, 'b' => '0', 'c' => 'ab000', 'd' => '3')
		);
		// must pass
		$results = array(
			'a'             => array(2, 0, 1, 3),
			'a as int'      => array(2, 0, 1, 3),
			'a as int asc'  => array(2, 0, 1, 3),
			'a asc'         => array(2, 0, 1, 3),
			'a as int desc' => array(3, 1, 0, 2),
			'a desc'        => array(3, 1, 0, 2),
			'a as str'      => array(2, 0, 1, 3),
			'b as str'      => array(3, 1, 0, 2),
			'b as str asc'  => array(3, 1, 0, 2),
			'b as str desc' => array(2, 0, 1, 3),
			'b as nat'      => array(3, 1, 0, 2),
			'b as int'      => array(3, 2, 1, 0),
			'c as nat'      => array(3, 1, 2, 0),
			'd, a'                  => array(2, 0, 1, 3),
			'd as int asc, a asc'   => array(2, 0, 1, 3),
			'd as int desc, a asc'  => array(3, 1, 2, 0),
			'd as int desc, a desc' => array(3, 1, 0, 2),
			'd asc, a as int desc'  => array(0, 2, 1, 3),
			// ' '  => false,
		);
		foreach ($results as $str => $rests) {
			$this->createTbsDataSourceInstance($data);
			$this->tbs->NoErr = TRUE;
			$true = $this->dataSrc->DataSort($str);
			if (!$true) {
				if ($rests === false) {
					$this->success();
				} else {
					$this->fail('SORTBY error. The function DataSort returns false. Query string: [sortby ' . $str . ']');
				}
				continue;
			}
			if ($rests === false) {
				$this->fail('SORTBY error. The function DataSort should return false in this case. Query string: [sortby ' . $str . ']');
				continue;
			}
			$result = array();
			foreach ($rests as $rkey) {
				$result[] = $data[$rkey];
			}
			$message = 'SORTBY error. Query string: [sortby ' . $str . ']';
			$this->assertEqual($this->dataSrc->SrcId, $result, $message);
		}
	}
	
}

