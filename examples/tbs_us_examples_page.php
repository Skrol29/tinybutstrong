<?php

include_once('tbs_class.php');
include_once('tbs_plugin_bypage.php'); // Plug-in By-Page
include_once('tbs_plugin_navbar.php'); // Plug-in Navigation Bar

$data[] = array('product'=>'Tuba'       , 'price'=>100.00);
$data[] = array('product'=>'Trumpet'    , 'price'=>112.50);
$data[] = array('product'=>'Trombone'   , 'price'=>169.00);
$data[] = array('product'=>'Banjo'      , 'price'=>119.00);
$data[] = array('product'=>'Cymbals'    , 'price'=>67.00);
$data[] = array('product'=>'Drums'      , 'price'=>269.95);
$data[] = array('product'=>'Flute'      , 'price'=>39.95);
$data[] = array('product'=>'Saxophone'  , 'price'=>760.00);
$data[] = array('product'=>'Piano'      , 'price'=>10995.00);
$data[] = array('product'=>'Organ'      , 'price'=>700.00);
$data[] = array('product'=>'Clarinet'   , 'price'=>56.00);
$data[] = array('product'=>'Guitar'     , 'price'=>215.00);
$data[] = array('product'=>'Harmonica'  , 'price'=>5.99);
$data[] = array('product'=>'Bass'       , 'price'=>189.00);
$data[] = array('product'=>'Harp'       , 'price'=>199.00);
$data[] = array('product'=>'Violin'     , 'price'=>64.95);
$data[] = array('product'=>'Bagpipes'   , 'price'=>129.00);
$data[] = array('product'=>'Ukulele'    , 'price'=>48.00);

// Default value
if (!isset($_GET)) $_GET=&$HTTP_GET_VARS;
if (isset($_GET['PageNum'])) {
  $PageNum = $_GET['PageNum'];
} else {
	$PageNum = 1; 
}

// Default value
if (isset($_GET['RecCnt'])) {
  $RecCnt = intval($_GET['RecCnt']);
} else {
	$RecCnt = -1;
}

$PageSize = 5;

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_page.htm');

// Merge the block by page
$TBS->PlugIn(TBS_BYPAGE,$PageSize,$PageNum,$RecCnt); // Next block will be merged suing By-Page mode.
$RecCnt = $TBS->MergeBlock('blk',$data);

// Merge the Navigation Bar
$TBS->PlugIn(TBS_NAVBAR,'nv','',$PageNum,$RecCnt,$PageSize);


$TBS->Show();

?>