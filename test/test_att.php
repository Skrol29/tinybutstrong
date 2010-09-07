<?php

if (intval(PHP_VERSION)>=5) {
	include_once('tbs_class_php5.php');
} else {
	include_once('tbs_class.php');
}

$am = array(); // test attribut avec mise ne cache du MergeBlock()
$am[] = array('id'=>'AttCache x12', 'maquille'=>'effet1');
$am[] = array('id'=>'AttCache x13', 'maquille'=>'effet2');
$am[] = array('id'=>'AttCache x14', 'maquille'=>'effet3');
$am[] = array('id'=>'AttCache x15', 'maquille'=>'effet4');

$deplace = "essai2";
$depvide = "";
$x = '';

$TBS = new clsTinyButStrong;

$TBS->LoadTemplate('test_att.html');

$TBS->MergeBlock('am1,am2,am3',$am); // ok
$TBS->MergeBlock('am4',$am);



$TBS->Show();



?>