<?php

include_once('tbs_class.php');

//Default value
if (!isset($_GET)) $_GET=&$HTTP_GET_VARS;
if (isset($_GET['empty'])) {
  $empty = $_GET['empty'];
} else {
  $empty = 0;
}

if ($empty) {
	$url = '';
	$image = '';
	$line1 = '1 New Avenue';
	$line2 = '';
} else {
	$url = 'www.tinybutstrong.com';
	$image = 'tbs_us_examples_prmmagnet.gif';
	$line1 = '2 Main Street';
	$line2 = '3rd floor';
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_prmmagnet.htm');
$TBS->Show();

?>