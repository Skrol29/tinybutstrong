<?php

include_once('tbs_class.php');

$country = array('France','England','Spain','Italy','Germany');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_blocks.htm');
$TBS->MergeBlock('blk1,blk2,blk3,blk4,blk5,blk6,blk7',$country); // Merge several blocks with the same data
$TBS->Show();

?>