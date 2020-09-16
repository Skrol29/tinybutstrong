<?php

class BlockTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('Basic MergeBlock Unit Tests');
	}

	function setUp() {
	}

	function tearDown() {
	}

	function testArrayParameter() {
	
		$value = array(
			// data for block 'a'
			'a' => array(
				'b'=>'c',
				'd'=>array('e'=>'f', 'g'=>'h')
			),
			// data for block 'A'
			'A' => array(
				'B'=>'C',
				'D'=>'E'
			),
			// data for blocks 'C', 'D' and 'E'
			'C,D,E' => array(0, 1, 2, 3)
		);

		// test with an empty value
		$this->assertErrorMergeBlockString("<p><b>[a.val;block=b]</b></p>", array('a'=>null), "test block with array parameter #1");
		$this->assertErrorMergeBlockString("<p><b>[a.val;block=b]</b></p>", array('a'=>false), "test block with array parameter #2");
		$this->assertEqualMergeBlockString("<p><b>[a.val;block=b]</b></p>", array('a'=>array()), "<p></p>", "test block with array parameter #3");
		$this->assertEqualMergeBlockString("<p><b>[a.#;block=b]</b></p>", array('a'=>array()), "<p></p>", "test block with array parameter #4");
		$this->assertEqualMergeBlockString("<p><b>[a.#]</b></p>", array('a'=>array()), "<p><b>0</b></p>", "test block with array parameter #5");
		$this->assertEqualMergeBlockString("<p><b>[a.$]</b></p>", array('a'=>array()), "<p><b></b></p>", "test block with array parameter #6");

		// test with an non empty value
		$this->assertErrorMergeBlockString("<p><b>[a.val;block=b]</b></p>", $value, "test block with array parameter #7");
		$this->assertEqualMergeBlockString("<p><b>[A.val;block=b]</b></p>", $value, "<p><b>C</b><b>E</b></p>", "test block with array parameter #8");
		$this->assertEqualMergeBlockString("<p><b>[A.key;block=b]</b></p>", $value, "<p><b>B</b><b>D</b></p>", "test block with array parameter #9");
		$this->assertEqualMergeBlockString("<p><b>[A.#;block=b]</b></p>", $value, "<p><b>1</b><b>2</b></p>", "test block with array parameter #10");
		$this->assertEqualMergeBlockString("<p><b>[A.$;block=b]</b></p>", $value, "<p><b>B</b><b>D</b></p>", "test block with array parameter #11");
		$this->assertEqualMergeBlockString("<p><b>[C.$;block=b]</b></p>", $value, "<p><b>0</b><b>1</b><b>2</b><b>3</b></p>", "test block with array parameter #12");
		$this->assertEqualMergeBlockString("<p><b>[C.#;block=b]</b></p>", $value, "<p><b>1</b><b>2</b><b>3</b><b>4</b></p>", "test block with array parameter #13");
		$this->assertEqualMergeBlockString("<p><b>[D.$;block=b]</b></p>", $value, "<p><b>0</b><b>1</b><b>2</b><b>3</b></p>", "test block with array parameter #14");
		$this->assertEqualMergeBlockString("<p><b>[E.#;block=b]</b></p>", $value, "<p><b>1</b><b>2</b><b>3</b><b>4</b></p>", "test block with array parameter #15");
		$this->assertErrorMergeBlockString("<p><b>[E.#;block=div]</b></p>", $value, "test block with array parameter #16");
		$this->assertEqualMergeBlockString("<b>[A.key;block=b;when [A.key]!=B]</b>", $value, "<b>D</b>", "test block with array parameter #17");

		// test with extended blocks
		$this->assertEqualMergeBlockString("<p><u>[A.val;block=u+v]</u><v>ok</v></p>", $value, "<p><u>C</u><v>ok</v><u>E</u><v>ok</v></p>", "test extended blocks #1");
		$this->assertEqualMergeBlockString("<p><u id='[A.val;block=u+v]' /><v>ok</v></p>", $value, "<p><u id='C' /><v>ok</v><u id='E' /><v>ok</v></p>", "test extended blocks #1bis");
		$this->assertEqualMergeBlockString("<p><u id='[A.val;block=u+v]'/><v>ok</v></p>", $value, "<p><u id='C'/><v>ok</v><u id='E'/><v>ok</v></p>", "test extended blocks #1bis");
		$this->assertEqualMergeBlockString("<p><u>[A.val;block=u+v+w]</u><v>ok</v><w /></p>", $value, "<p><u>C</u><v>ok</v><w /><u>E</u><v>ok</v><w /></p>", "test extended blocks #2");
		$this->assertEqualMergeBlockString("<p><u>[A.val;block= u + v + w ]</u><v>ok</v><w /></p>", $value, "<p><u>C</u><v>ok</v><w /><u>E</u><v>ok</v><w /></p>", "test extended blocks #3");
		$this->assertEqualMergeBlockString("<p><u>[A.val;block=(u)+v+w]</u><v>ok</v><w /></p>", $value, "<p><u>C</u><v>ok</v><w /><u>E</u><v>ok</v><w /></p>", "test extended blocks #4");
		$this->assertEqualMergeBlockString("<p><u>ok</u><v>[A.val;block=u+(v)+w]</v><w /></p>", $value, "<p><u>ok</u><v>C</v><w /><u>ok</u><v>E</v><w /></p>", "test extended blocks #5");
		$this->assertEqualMergeBlockString("<p><u>ok</u><v><v>[A.val;block=u+((v))+w]</v><z /></v><w /></p>", $value, "<p><u>ok</u><v><v>C</v><z /></v><w /><u>ok</u><v><v>E</v><z /></v><w /></p>", "test extended blocks #5");

		// test multiple blocks
		$this->assertEqualMergeBlockString("<p><b>[C.#;block=b][D.#;block=b]</b></p>", $value, "<p><b>11</b><b>22</b><b>33</b><b>44</b></p>", "test block with array parameter #18");
		$this->assertEqualMergeBlockString("<p><b>[C.val;block=b][D.val;block=p]</b></p>", $value, "<p><b>00</b><b>10</b><b>20</b><b>30</b></p><p><b>01</b><b>11</b><b>21</b><b>31</b></p><p><b>02</b><b>12</b><b>22</b><b>32</b></p><p><b>03</b><b>13</b><b>23</b><b>33</b></p>", "test block with array parameter #19");
		$this->assertEqualMergeBlockString("<p><b>[C.#;block=b][C.#;block=b]</b></p>", $value, "<p><b>11</b><b>22</b><b>33</b><b>44</b></p>", "test block with array parameter #20");
		$this->assertEqualMergeBlockString("<p><b>[C.#;block=b;when [C.#]=1]</b><u>[C.#;block=u;when [C.#]!=1]</u></p>", $value, "<p><b>1</b><u>2</u><u>3</u><u>4</u></p>", "test block with array parameter #21");
		$this->assertEqualMergeBlockString("<p><b>[C.#;block=b;when [C.#]=1]</b><u>[C.#;block=u;when [C.#]=4]</u><a>[C.#;block=a;when [C.#]=0]</a><s>[C.#;block=s;when [C.#]!=0]</s></p>", $value, "<p><b>1</b><s>2</s><s>3</s><u>4</u></p>", "test block with array parameter #22");

		// chain call
		$this->assertEqualMergeBlockString("<b>[a.e;block=b;when [a.#]=2]</b>", $value, "<b>f</b>", "test block with array parameter #23");
	}

	function testFieldPosition() {
	
		$blocks = array(
			'a' => array(
				array('title' => "AAA", 'id'=>21),
				array('title' => "BBB", 'id'=>22),
				array('title' => "CCC", 'id'=>23),
			),
		);

		$this->assertEqualMergeBlockString("<p><b>[a.title;block=b]</b></p>", $blocks, "<p><b>AAA</b><b>BBB</b><b>CCC</b></p>", "test block with standard field position");
		$this->assertEqualMergeBlockString("<p><b>[a.title;block=b;if 22=[a.id];then 'XXX']</b></p>", $blocks, "<p><b>AAA</b><b>XXX</b><b>CCC</b></p>", "test block with embedded field");
		
	}
	
	function testNumParameter() {
		// $this->dumpLastSource();
	}

	function testBugs() {
		$value = array('a'=>array('b'=>'c', 'd'=>$this), 'A'=>array('B'=>'C', 'D'=>'E'), 'C,D,E'=>array(0, 1, 2, 3));

		// count block records bug
		// $this->assertEqualMergeBlockString("<p><b>[C.#]</b></p>", $value, "<p><b>4</b></p>", "count block records bug");

		// block with conditions erase html beetween blocks
		// $this->assertEqualMergeBlockString("<p><b>[C.#;block=b;when [C.#]=1]</b><t>text</t><u>[C.#;block=u;when [C.#]!=1]</u></p>", $value, "<p><b>1</b><t>text</t><u>2</u><u>3</u><u>4</u></p>", "html erased beetween blocks bug");

		// bad block syntax should display a TBS error
		// $this->assertErrorMergeBlockString("<p><b>[a;block=b]</b></p>", $value, "bug with bad block syntax");

		// double block selection should display a TBS error
		// $this->assertErrorMergeBlockString("<p><b>[C.#;block=b][C.#;block=p]</b></p>", $value, "double block selection bug");
	}
	
	function testNoBoundSyntax() {
	
		$blocks = array(
			'a' => array(
				array('title' => "AAA", 'id'=>21),
				array('title' => "BBB", 'id'=>22),
				array('title' => "CCC", 'id'=>23),
			),
		);

		if ( $this->atLeastTBSVersion('3.10.0') ) {
			
			// With NotBounds
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=!b]</b></p>", $blocks, "<p><b>AAABBBCCC</b></p>", "test block=!b");
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=(!b)]</b></p>", $blocks, "<p><b>AAABBBCCC</b></p>", "test block=(!b)");
			$this->assertEqualMergeBlockString("<p><b><b>[a.title;block=((!b))]</b></b></p>", $blocks, "<p><b><b>AAA</b><b>BBB</b><b>CCC</b></b></p>", "test block=((!b))");
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=b+!c]</b>-<c>ok</c></p>", $blocks, "<p><b>AAA</b>-<b>BBB</b>-<b>CCC</b>-<c>ok</c></p>", "test block=b+!c");
			$this->assertEqualMergeBlockString("<p><c>ok</c>-<b>[a.title;block=!c+(b)]</b></p>", $blocks, "<p><c>ok</c>-<b>AAA</b>-<b>BBB</b>-<b>CCC</b></p>", "test block=!c+(b)");
			$this->assertEqualMergeBlockString("<p><c>ok</c>-[a.title;block=!c+(.)+!b]:<b>here</b></p>", $blocks, "<p><c>ok</c>-AAA:-BBB:-CCC:<b>here</b></p>", "test block=!c+(b)");
			
			// With dot
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=.]</b></p>", $blocks, "<p><b>AAABBBCCC</b></p>", "test block=.");
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=(.)]</b></p>", $blocks, "<p><b>AAABBBCCC</b></p>", "test block=(.)");
			$this->assertEqualMergeBlockString("<p><b>[a.title;block=(.)+c]-<c>ok</c></b></p>", $blocks, "<p><b>AAA-<c>ok</c>BBB-<c>ok</c>CCC-<c>ok</c></b></p>", "test block=(.)+c");
			$this->assertEqualMergeBlockString("<p><b><c>ok</c>-[a.title;block=c+(.)]</b></p>", $blocks, "<p><b><c>ok</c>-AAA<c>ok</c>-BBB<c>ok</c>-CCC</b></p>", "test block=c+(.)");
			$this->assertEqualMergeBlockString("<p><b><c>ok</c>-[a.title;block=c+.]</b></p>"  , $blocks, "<p><b><c>ok</c>-AAA<c>ok</c>-BBB<c>ok</c>-CCC</b></p>", "test block=c+.");

			
		}
		
	}

	function testMultiplierSyntax() {
	
		$blocks = array(
			'a' => array(
				array('title' => "AAA", 'id'=>21),
				array('title' => "BBB", 'id'=>22),
				array('title' => "CCC", 'id'=>23),
			),
		);

		if ( $this->atLeastTBSVersion('3.10.0') ) {

			$this->assertEqualMergeBlockString("<p><b>[a.title;block=2*b]</b><b>more</b></p>", $blocks, "<p><b>AAA</b><b>more</b><b>BBB</b><b>more</b><b>CCC</b><b>more</b></p>", "test block=2*b");
			
		}
		
	}	

	function testMergeBlockResult() {
	
		$blocks = array(
			'a' => array(
				array('title' => "AAA", 'id'=>21),
				array('title' => "BBB", 'id'=>22),
				array('title' => "CCC", 'id'=>23),
			),
		);

		$this->assertEqualMergeBlockResult("<p>[a.id;block=p]</p>", $blocks, 3, "test bloc standard.");

		$this->assertEqualMergeBlockResult("<p>[a.id]</p>", $blocks, 1, "test champ simple.");
		
		if ( $this->atLeastTBSVersion('3.12.0-beta') ) { // valable aussi si version = '3.12.0'
			$this->assertEqualMergeBlockResult("<p>[xxx.id]</p>", $blocks, false, "test bloc absent (à partir de TBS 3.12).");
		} else {
			$this->assertEqualMergeBlockResult("<p>[xxx.id]</p>", $blocks, 0, "test bloc absent.");
		}
	}
	
}

