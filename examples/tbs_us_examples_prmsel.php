<?php

include_once('tbs_class.php');
include_once('tbs_plugin_html.php'); // Plug-in to select HTML items.

// Example with values stored in an array.
$item_lst = array();
$item_lst[] = array('name'=>'Red','id'=>1);
$item_lst[] = array('name'=>'Green' ,'id'=>2);
$item_lst[] = array('name'=>'Blue' ,'id'=>3);
$item_lst[] = array('name'=>'Yellow','id'=>4);
$item_lst[] = array('name'=>'White','id'=>5);

$sel1_name = 'Yellow';
$sel1_id = 4;
$sel2_name = array('Green','Blue','Yellow');
$sel2_id = array(2,3,4);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_prmsel.htm');

// List feeding
$TBS->MergeBlock('lst1v',$item_lst);
$TBS->MergeBlock('lst1' ,$item_lst);
$TBS->MergeBlock('lst3v',$item_lst);
$TBS->MergeBlock('lst3' ,$item_lst);

// The selection of the items is done here
$TBS->Show();

?>