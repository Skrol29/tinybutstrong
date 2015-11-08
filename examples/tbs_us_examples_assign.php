<?php

include_once('tbs_class.php');

$data = array();
$data[] = array('cmt'=>'Assigned data');
$data[] = array('cmt'=>'is something that');
$data[] = array('cmt'=>'can be quite powerful.');

$TBS = new clsTinyButStrong;

// Assign data for an automatic merging
$TBS->Assigned['expl_1'] = array('b_auto', &$data, 'auto'=>'onload'); // Note that the data array is given by reference in order to prevent from copying the data in memory.

// Assign data for a manual merging
$TBS->Assigned['expl_2'] = array('b_manual', &$data);

// Loas the template, 'expl_1' is merged then.
$TBS->LoadTemplate('tbs_us_examples_assign.htm');

// manual merging of assigned data
$TBS->MergeBlock('expl_2', 'assigned');

$TBS->Show();

?>