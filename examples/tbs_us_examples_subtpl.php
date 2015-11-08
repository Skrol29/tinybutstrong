<?php

if (!isset($viewer)) $viewer = 'tbs_us_examples.php';

// Check if subscript's source is asked.
if (isset($_GET['subsrc'])) {
  show_source('tbs_us_examples_subtpl_login.php');
  exit;
}

// Prepare variabes 
if (isset($_GET['art'])) {
	$art = $_GET['art'];
}	else {
	$art = 0;
}
$tmpl_article = 'tbs_us_examples_subtpl_article'.$art.'.htm';
$tmpl_menu = 'tbs_us_examples_subtpl_menu.htm';

// Merging main template
include_once('tbs_class.php');
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_subtpl.htm');
$TBS->Show();

?>