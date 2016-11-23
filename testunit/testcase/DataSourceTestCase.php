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
			// 'E'  => array(3, 2, 1, 0),
		);
		foreach ($results as $str => $rests) {
			$this->createTbsDataSourceInstance($data);
			$this->tbs->NoErr = TRUE;
			$true = $this->dataSrc->DataSort($str);
			if (!$true) {
				if ($rests === false) {
					$this->pass();
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
			$this->assertEqual($this->dataSrc->SrcId, $result, 'SORTBY error. Query string: [sortby ' . $str . ']');
		}
	}

	function testGroupBy() {
		$data = array(
			array('a' => 1, 'b' => 'a', 'd' => '-2'),
			array('a' => 1, 'b' => 'b', 'd' => '+8'),
			array('a' => 2, 'b' => 'b', 'd' => '+8'),
			array('a' => 2, 'b' => 'a', 'd' => '+8')
		);
		// must pass
		$variant1 = array(
			array(
				'a' => 1,
				'group' => array(
					array('a' => 1, 'b' => 'a', 'd' => '-2'),
					array('a' => 1, 'b' => 'b', 'd' => '+8'),
				),
			),
			array(
				'a' => 2,
				'group' => array(
					array('a' => 2, 'b' => 'b', 'd' => '+8'),
					array('a' => 2, 'b' => 'a', 'd' => '+8')
				),
			),
		);
		$variant2 = array(
			array(
				'b' => 'a',
				'd' => '-2',
				'group' => array(
					array('a' => 1, 'b' => 'a', 'd' => '-2'),
				),
			),
			array(
				'b' => 'b',
				'd' => '+8',
				'group' => array(
					array('a' => 1, 'b' => 'b', 'd' => '+8'),
					array('a' => 2, 'b' => 'b', 'd' => '+8'),
				),
			),
			array(
				'b' => 'a',
				'd' => '+8',
				'group' => array(
					array('a' => 2, 'b' => 'a', 'd' => '+8')
				),
			),
		);
		$variant3 = array(
			array(
				'a' => 1,
				'b' => 'a',
				'd' => '-2',
				'group' => array(
					array('a' => 1, 'b' => 'a', 'd' => '-2'),
				),
			),
			array(
				'a' => 1,
				'b' => 'b',
				'd' => '+8',
				'group' => array(
					array('a' => 1, 'b' => 'b', 'd' => '+8'),
				),
			),
			array(
				'a' => 2,
				'b' => 'b',
				'd' => '+8',
				'group' => array(
					array('a' => 2, 'b' => 'b', 'd' => '+8'),
				),
			),
			array(
				'a' => 2,
				'b' => 'a',
				'd' => '+8',
				'group' => array(
					array('a' => 2, 'b' => 'a', 'd' => '+8')
				),
			),
		);
		$variant4 = array(
			array(
				'A' => null,
				'B' => null,
				'D' => null,
				'group' => array(
					array('a' => 1, 'b' => 'a', 'd' => '-2'),
					array('a' => 1, 'b' => 'b', 'd' => '+8'),
					array('a' => 2, 'b' => 'b', 'd' => '+8'),
					array('a' => 2, 'b' => 'a', 'd' => '+8')
				),
			),
		);
		$results = array(
			'a '              => $variant1,
			'a into group'    => $variant1,
			'b, d'            => $variant2,
			'd, b'            => $variant2,
			'b, d into group' => $variant2,
			'a, b, d'         => $variant3,
			'a     , b ,d'    => $variant3,
			'd, a, b'         => $variant3,
			'd, a, b, d, a'   => $variant3,
			'a,a,a,a,a,d,b'   => $variant3,
			'A,B,D'           => $variant4,
		);
		foreach ($results as $str => $result) {
			$this->createTbsDataSourceInstance($data);
			$this->tbs->NoErr = TRUE;
			$true = $this->dataSrc->DataGroup($str);
			if (!$true) {
				if ($result === false) {
					$this->pass();
				} else {
					$this->fail('GROUPBY error. The function DataGroup returns false. Query string: [groupby ' . $str . ']');
				}
				continue;
			}
			if ($result === false) {
				$this->fail('GROUPBY error. The function DataGroup should return false in this case. Query string: [groupby ' . $str . ']');
				continue;
			}
			// if ($this->dataSrc->SrcId != $result) {
				// print_r($result);
				// print_r($this->dataSrc->SrcId);
			// }
			$this->assertEqual($this->dataSrc->SrcId, $result, 'GROUPBY error. Query string: [groupby ' . $str . ']');
		}
	}

	function testGroupByFlags() {
		$el1 = array('a' => 1, 'b' => 'a', 'd' => '-2', 'e' => array(1, 2,));
		$el2 = array('a' => 1, 'b' => 'b', 'd' => '+8', 'e' => array(2, 3,));
		$el3 = array('a' => 2, 'b' => 'b', 'd' => '+8', 'e' => array(null, 3,));
		$el4 = array('a' => 2, 'b' => 'a', 'd' => '+8', 'e' => null);
		$data = array(
			$el1,
			$el2,
			$el3,
			$el4,
		);
		// must pass
		$variant1 = array(
			array(
				'e' => array(1, 2,),
				'group' => array(
					$el1,
				),
			),
			array(
				'e' => $el2['e'],
				'group' => array(
					$el2,
				),
			),
			array(
				'e' => $el3['e'],
				'group' => array(
					$el3,
				),
			),
			array(
				'e' => $el4['e'],
				'group' => array(
					$el4,
				),
			),
		);
		$variant2 = array(
			array(
				'e' => 1,
				'group' => array(
					$el1,
				),
			),
			array(
				'e' => 2,
				'group' => array(
					$el1,
					$el2,
				),
			),
			array(
				'e' => 3,
				'group' => array(
					$el2,
					$el3,
				),
			),
			array(
				'e' => null,
				'group' => array(
					$el3,
					$el4,
				),
			),
		);
		$variant3 = array(
			array(
				'a' => 1,
				'e' => 1,
				'group' => array(
					$el1,
				),
			),
			array(
				'a' => 1,
				'e' => 2,
				'group' => array(
					$el1,
					$el2,
				),
			),
			array(
				'a' => 1,
				'e' => 3,
				'group' => array(
					$el2,
				),
			),
			array(
				'a' => 2,
				'e' => null,
				'group' => array(
					$el3,
					$el4,
				),
			),
			array(
				'a' => 2,
				'e' => 3,
				'group' => array(
					$el3,
				),
			),
		);
		$results = array(
			'e '              => $variant1,
			'e asFlags'    => $variant2,
			'a, e asFlags' => $variant3,
		);
		foreach ($results as $str => $result) {
			$this->createTbsDataSourceInstance($data);
			$this->tbs->NoErr = TRUE;
			$true = $this->dataSrc->DataGroup($str);
			if (!$true) {
				if ($result === false) {
					$this->pass();
				} else {
					$this->fail('GROUPBY FLAG error. The function DataGroup returns false. Query string: [groupby ' . $str . ']');
				}
				continue;
			}
			if ($result === false) {
				$this->fail('GROUPBY FLAG error. The function DataGroup should return false in this case. Query string: [groupby ' . $str . ']');
				continue;
			}
			// if ($this->dataSrc->SrcId != $result) {
				// print_r($result);
				// print_r($this->dataSrc->SrcId);
			// }
			$this->assertEqual($this->dataSrc->SrcId, $result, 'GROUPBY FLAG error. Query string: [groupby ' . $str . ']');
		}
	}

	function testGroupCalc() {
		$el1 = array('a' => 1, 'b' => 'a', 'd' => 'x', 'e' => 32);
		$el2 = array('a' => 2, 'b' => 'a', 'd' => 'a', 'e' => 64);
		$el3 = array('a' => 4, 'b' => 'a', 'd' => 'a', 'e' => 128);
		$el4 = array('a' => 8, 'b' => 'x', 'd' => 'a', 'e' => 16);
		$data = array(
			$el1,
			$el2,
			$el3,
			$el4,
		);
		// must pass
		$variant1 = array(
			array(
				'b' => $el1['b'],
				'group' => array(
					$el1,
					$el2,
					$el3,
				),
				'a_sum' => 7,
			),
			array(
				'b' => $el4['b'],
				'group' => array(
					$el4,
				),
				'a_sum' => 8,
			),
		);
		$variant2 = array(
			array(
				'b' => $el1['b'],
				'group' => 231,
			),
			array(
				'b' => $el4['b'],
				'group' => 24,
			),
		);
		$variant3 = array(
			array(
				'b' => $el1['b'],
				'd' => $el1['d'],
				'group' => 33,
			),
			array(
				'b' => $el2['b'],
				'd' => $el2['d'],
				'group' => 198,
			),
			array(
				'b' => $el4['b'],
				'd' => $el4['d'],
				'group' => 24,
			),
		);
		$results = array(
			array (
				'b',
				'sum a into a_sum',
				$variant1,
			),
			array (
				'b',
				'sum a e into group',
				$variant2,
			),
			array (
				'b, d into group',
				'sum a,  e into group',
				$variant3,
			),
		);
		foreach ($results as $item) {
			$groupStr = $item[0];
			$calcStr = $item[1];
			$result = $item[2];
			$this->createTbsDataSourceInstance($data);
			$this->tbs->NoErr = TRUE;
			$true = $this->dataSrc->DataGroup($groupStr, $calcStr);
			if (!$true) {
				if ($result === false) {
					$this->pass();
				} else {
					$this->fail("GROUPBY CALC error. The function DataGroup returns false. Query string: [groupby {$groupStr};groupcalc {$calcStr}]");
				}
				continue;
			}
			if ($result === false) {
				$this->fail("GROUPBY CALC error. The function DataGroup should return false in this case. Query string: [groupby {$groupStr};groupcalc {$calcStr}]");
				continue;
			}
//			 if ($this->dataSrc->SrcId != $result) {
//				 print_r($result);
//				 print_r($this->dataSrc->SrcId);
//			 }
			$this->assertEqual($this->dataSrc->SrcId, $result, "GROUPBY CALC error. Query string: [groupby {$groupStr};groupcalc {$calcStr}]");
		}
	}
	
}

