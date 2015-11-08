<?php

include_once('tbs_class.php');

$array_type1 = array('France'=>33, 'England'=>44, 'Spain'=>34, 'Italy'=>39, 'Deutchland'=>49);

$array_type2[] = array('res_name'=>'Marie',  'res_score'=>300, 'res_date'=>'2003-01-10');
$array_type2[] = array('res_name'=>'Eric', 'res_score'=>215, 'res_date'=>'2003-01-10');
$array_type2[] = array('res_name'=>'Mark', 'res_score'=>180, 'res_date'=>'2003-01-10');
$array_type2[] = array('res_name'=>'Paul', 'res_score'=>175, 'res_date'=>'2003-01-10');
$array_type2[] = array('res_name'=>'Mat', 'res_score'=>120, 'res_date'=>'2003-01-10');
$array_type2[] = array('res_name'=>'Sonia', 'res_score'=>115, 'res_date'=>'2003-01-10');

$all_array['type1'] = $array_type1;
$all_array['type2'] = $array_type2;

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_dataarray.htm');
$TBS->MergeBlock('blk1',$array_type1);
$TBS->MergeBlock('blk2',$array_type2);
$TBS->MergeBlock('blk3','array','all_array[type2]');
$TBS->Show();

?>