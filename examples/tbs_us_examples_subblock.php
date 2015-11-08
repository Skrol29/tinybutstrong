<?php

include_once('tbs_class.php');

// Create data
$TeamList[0] = array('team'=>'Eagle'  ,'total'=>'458');
$TeamList[0]['matches'][] = array('town'=>'London','score'=>'253','date'=>'1999-11-30');
$TeamList[0]['matches'][] = array('town'=>'Paris' ,'score'=>'145','date'=>'2002-07-24');
$TeamList[1] = array('team'=>'Goonies','total'=>'281');
$TeamList[1]['matches'][] = array('town'=>'New-York','score'=>'365','date'=>'2001-12-25');
$TeamList[1]['matches'][] = array('town'=>'Madrid'  ,'score'=>'521','date'=>'2004-01-14');
$TeamList[2] = array('team'=>'MIB'    ,'total'=>'615');
$TeamList[2]['matches'][] = array('town'=>'Dallas'    ,'score'=>'362','date'=>'2001-01-02');
$TeamList[2]['matches'][] = array('town'=>'Lyon'      ,'score'=>'321','date'=>'2002-11-17');
$TeamList[2]['matches'][] = array('town'=>'Washington','score'=>'245','date'=>'2003-08-24');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_subblock.htm');

// Automatic subblock
$TBS->MergeBlock('asb',$TeamList);

// Subblock with a dynamic query
$TBS->MergeBlock('mb','array','TeamList');
$TBS->MergeBlock('sb','array','TeamList[%p1%][matches]');


$TBS->Show();

?>