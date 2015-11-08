<?php

include_once('tbs_class.php');

$data_array[] = array('res_name'=>'Marie',  'res_score'=>300, 'res_date'=>'2003-01-10');
$data_array[] = array('res_name'=>'Eric', 'res_score'=>215, 'res_date'=>'2003-01-10');
$data_array[] = array('res_name'=>'Mark', 'res_score'=>180, 'res_date'=>'2003-01-10');
$data_array[] = array('res_name'=>'Paul', 'res_score'=>175, 'res_date'=>'2003-01-10');
$data_array[] = array('res_name'=>'Math', 'res_score'=>120, 'res_date'=>'2003-01-10');
$data_array[] = array('res_name'=>'Lucy', 'res_score'=>115, 'res_date'=>'2003-01-10');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_event.htm');
$TBS->MergeBlock('b1',$data_array);
$TBS->Show();

//Event functions
function m_event_b1($BlockName,&$CurrRec,$RecNum){
//$BlockName : name of the block that calls the function (read only)
//$CurrRec   : array that contains columns of the current record (read/write)
//$RecNum    : number of the current record (read only)
  if ($RecNum==1) $CurrRec['res_name'] = $CurrRec['res_name']. ' (WINS)';
  if ($CurrRec['res_score']<100) $CurrRec['level'] = 'bad';
  if ($CurrRec['res_score']>=100) $CurrRec['level'] = '<font color="#669933">middle</font>';
  if ($CurrRec['res_score']>=200) $CurrRec['level'] = '<font color="#3366CC">good</font>';
  if ($CurrRec['res_score']>=300) $CurrRec['level'] = '<font color="#CCCC00"><strong>excellent</strong></font>';
}

?>