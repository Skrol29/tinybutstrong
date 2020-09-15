<?php

class BlockGrpTestCase extends TBSUnitTestCase {

	function __construct() {
		$this->UnitTestCase('MergeBlock Unit Tests with Groups');
	}

	function setUp() {
	}

	function tearDown() {
	}

    private function _getData() {
        
		$data = array(
			array('cat1'=>'A', 'cat2'=>'x', 'cat3'=>'j', 'catU'=>'U', 'name'=>'n01'),
			array('cat1'=>'A', 'cat2'=>'x', 'cat3'=>'j', 'catU'=>'U', 'name'=>'n02'),
			array('cat1'=>'A', 'cat2'=>'x', 'cat3'=>'k', 'catU'=>'U', 'name'=>'n03'),
			array('cat1'=>'B', 'cat2'=>'x', 'cat3'=>'k', 'catU'=>'U', 'name'=>'n04'),
			array('cat1'=>'B', 'cat2'=>'y', 'cat3'=>'m', 'catU'=>'U', 'name'=>'n05'),
			array('cat1'=>'B', 'cat2'=>'y', 'cat3'=>'m', 'catU'=>'U', 'name'=>'n06'),
			array('cat1'=>'C', 'cat2'=>'y', 'cat3'=>'n', 'catU'=>'U', 'name'=>'n07'),
			array('cat1'=>'C', 'cat2'=>'y', 'cat3'=>'n', 'catU'=>'U', 'name'=>'n08'),
			array('cat1'=>'C', 'cat2'=>'y', 'cat3'=>'q', 'catU'=>'U', 'name'=>'n09'),
		);
        
        return $data;
        
    }

    private function _getNoData() {
		$data = array();
        return $data;
    }

    private function _getSingleData() {
		$data = array(
			array('cat1'=>'A', 'cat2'=>'x', 'cat3'=>'j', 'catU'=>'U', 'name'=>'n01'),
		);
        return $data;
    }
    
	function testSplitterGrp() {
	
        $data = $this->_getData();
        $data_no = $this->_getNoData();
        $data_single = $this->_getSingleData();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;splittergrp=cat1]</B>}'
            , array('a'=>$data)
            , '{<p>n01</p><p>n02</p><p>n03</p><B>n03</B><p>n04</p><p>n05</p><p>n06</p><B>n06</B><p>n07</p><p>n08</p><p>n09</p>}'
            , "splittergrp with several values");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;splittergrp=cat1]</B>}'
            , array('a'=>$data_no)
            , '{}'
            , "splittergrp with no values");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;splittergrp=cat1]</B>}'
            , array('a'=>$data_single)
            , '{<p>n01</p>}'
            , "splittergrp with no values");

        //$this->dumpLastSource();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;splittergrp=catU]</B>}'
            , array('a'=>$data)
            , '{<p>n01</p><p>n02</p><p>n03</p><p>n04</p><p>n05</p><p>n06</p><p>n07</p><p>n08</p><p>n09</p>}'
            , "splittergrp with unique value");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]:[a.cat1],[a.cat2],[a.cat3]</p><A>[a.name;block=A;splittergrp=cat1]</A><B>[a.name;block=B;splittergrp=cat2]</B><C>[a.name;block=C;splittergrp=cat3]</C><U>[a.name;block=U;splittergrp=catU]</U>}'
            , array('a'=>$data)
            , '{<p>n01:A,x,j</p><p>n02:A,x,j</p><C>n02</C><p>n03:A,x,k</p><A>n03</A><p>n04:B,x,k</p><B>n04</B><C>n04</C><p>n05:B,y,m</p><p>n06:B,y,m</p><A>n06</A><C>n06</C><p>n07:C,y,n</p><p>n08:C,y,n</p><C>n08</C><p>n09:C,y,q</p>}'
            , "splittergrp with several groups");
            
        //$this->dumpLastSource();
        
    }

	function testHeaderGrp() {
	
        $data = $this->_getData();
        $data_no = $this->_getNoData();
        $data_single = $this->_getSingleData();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;headergrp=cat1]</B>}'
            , array('a'=>$data)
            , '{<B>n01</B><p>n01</p><p>n02</p><p>n03</p><B>n04</B><p>n04</p><p>n05</p><p>n06</p><B>n07</B><p>n07</p><p>n08</p><p>n09</p>}'
            , "headergrp with several values");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;headergrp=cat1]</B>}'
            , array('a'=>$data_no)
            , '{}'
            , "headergrp with no values");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;headergrp=cat1]</B>}'
            , array('a'=>$data_single)
            , '{<B>n01</B><p>n01</p>}'
            , "headergrp with no values");

        //$this->dumpLastSource();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;headergrp=catU]</B>}'
            , array('a'=>$data)
            , '{<B>n01</B><p>n01</p><p>n02</p><p>n03</p><p>n04</p><p>n05</p><p>n06</p><p>n07</p><p>n08</p><p>n09</p>}'
            , "headergrp with unique value");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]:[a.cat1],[a.cat2],[a.cat3]</p><A>[a.name;block=A;headergrp=cat1]</A><B>[a.name;block=B;headergrp=cat2]</B><C>[a.name;block=C;headergrp=cat3]</C><U>[a.name;block=U;headergrp=catU]</U>}'
            , array('a'=>$data)
            , '{<A>n01</A><B>n01</B><C>n01</C><U>n01</U><p>n01:A,x,j</p><p>n02:A,x,j</p><C>n03</C><U>n03</U><p>n03:A,x,k</p><A>n04</A><B>n04</B><C>n04</C><U>n04</U><p>n04:B,x,k</p><B>n05</B><C>n05</C><U>n05</U><p>n05:B,y,m</p><p>n06:B,y,m</p><A>n07</A><B>n07</B><C>n07</C><U>n07</U><p>n07:C,y,n</p><p>n08:C,y,n</p><C>n09</C><U>n09</U><p>n09:C,y,q</p>}'
            , "headergrp with several groups");
            
        //$this->dumpLastSource();        
        
    }

	function testFooterGrp() {
	
        $data = $this->_getData();
        $data_no = $this->_getNoData();
        $data_single = $this->_getSingleData();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;footergrp=cat1]</B>}'
            , array('a'=>$data)
            , '{<p>n01</p><p>n02</p><p>n03</p><B>n03</B><p>n04</p><p>n05</p><p>n06</p><B>n06</B><p>n07</p><p>n08</p><p>n09</p><B>n09</B>}'
            , "footergrp with several values");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;footergrp=cat1]</B>}'
            , array('a'=>$data_no)
            , '{}'
            , "footergrp with no values");


		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;footergrp=cat1]</B>}'
            , array('a'=>$data_single)
            , '{<p>n01</p><B>n01</B>}'
            , "footergrp with no values");

        //$this->dumpLastSource();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><B>[a.name;block=B;footergrp=catU]</B>}'
            , array('a'=>$data)
            , '{<p>n01</p><p>n02</p><p>n03</p><p>n04</p><p>n05</p><p>n06</p><p>n07</p><p>n08</p><p>n09</p><B>n09</B>}'
            , "footergrp with unique value");

        //$this->dumpLastSource();

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]:[a.cat1],[a.cat2],[a.cat3]</p><A>[a.name;block=A;footergrp=cat1]</A><B>[a.name;block=B;footergrp=cat2]</B><C>[a.name;block=C;footergrp=cat3]</C><U>[a.name;block=U;footergrp=catU]</U>}'
            , array('a'=>$data)
            , '{<p>n01:A,x,j</p><p>n02:A,x,j</p><A>n02</A><B>n02</B><C>n02</C><p>n03:A,x,k</p><A>n03</A><p>n04:B,x,k</p><A>n04</A><B>n04</B><C>n04</C><p>n05:B,y,m</p><p>n06:B,y,m</p><A>n06</A><B>n06</B><C>n06</C><p>n07:C,y,n</p><p>n08:C,y,n</p><A>n08</A><B>n08</B><C>n08</C><p>n09:C,y,q</p><A>n09</A><B>n09</B><C>n09</C><U>n09</U>}'
            , "footergrp with several groups");
            
        //$this->dumpLastSource();        
        
    }

	function testRelatedGrp() {
	
        $data = array(
			array('cat1'=>'A', 'name'=>'n01'),
			array('cat1'=>'A', 'name'=>'n02'),
			array('cat1'=>'A', 'name'=>'n03'),
			array('cat1'=>'B', 'name'=>'n04'),
			array('cat1'=>'C', 'name'=>'n05'),
			array('cat1'=>'C', 'name'=>'n06'),
			array('cat1'=>'C', 'name'=>'n07'),
		);
		
		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><b>[a.name;block=b;firstingrp=cat1]</b><u>[a.name;block=u;lastingrp=cat1]</u><i>[a.name;block=i;singleingrp=cat1]</i>}'
            , array('a'=>$data)
            , '{<b>n01</b><p>n02</p><u>n03</u><i>n04</i><b>n05</b><p>n06</p><u>n07</u>}'
            , "related grp - order 1");

		$this->assertEqualMergeBlockString(
              '{<i>[a.name;block=i;singleingrp=cat1]</i><b>[a.name;block=b;firstingrp=cat1]</b><u>[a.name;block=u;lastingrp=cat1]</u><p>[a.name;block=p]</p>}'
            , array('a'=>$data)
            , '{<b>n01</b><p>n02</p><u>n03</u><i>n04</i><b>n05</b><p>n06</p><u>n07</u>}' // same result as previous one
            , "related grp - order 2");

		$this->assertEqualMergeBlockString(
              '{<p>[a.name;block=p]</p><i>[a.name;block=i;singleingrp=cat1]</i><u>[a.name;block=u;lastingrp=cat1]</u><b>[a.name;block=b;firstingrp=cat1]</b>}'
            , array('a'=>$data)
            , '{<b>n01</b><p>n02</p><u>n03</u><i>n04</i><b>n05</b><p>n06</p><u>n07</u>}' // same result as previous one
            , "related grp - order 3");
        
    }
	
}

