<?php

include_once('tbs_class.php');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_datanum.htm');
$TBS->MergeBlock('blk1','num',7);
$TBS->MergeBlock('blk2','num',array('min'=>-17,'max'=>0,'step'=>-2));
$TBS->Show();

?>