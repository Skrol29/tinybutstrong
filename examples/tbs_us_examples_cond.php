<?php

include_once('tbs_class.php');
include_once('tbs_plugin_html.php'); // Plug-in for selecting HTML items.

if (!isset($_GET)) $_GET=&$HTTP_GET_VARS;
if (isset($_GET['blk_id'])){
  $blk_id = $_GET['blk_id'];
} else {
  $blk_id = 0;
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_cond.htm');
$TBS->Show();

?>