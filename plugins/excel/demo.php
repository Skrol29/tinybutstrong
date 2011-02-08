<?php

if (count($_GET)==0) {
	header('Location: demo_main.htm');
	exit;
}

// load the TinyButStrong library
if (version_compare(PHP_VERSION,'5')<0) {
	include_once('tbs_class.php'); // TinyButStrong template engine for PHP 4
} else {
	include_once('tbs_class_php5.php'); // TinyButStrong template engine
}

// Excel plug-in for TBS 
include('tbs_plugin_excel.php');

include('demo_data.php'); // Data stored in arrays

$TBS = new clsTinyButStrong;

// Install the Excel plug-in (must be before LoadTemplate)
$TBS->PlugIn(TBS_INSTALL, TBS_EXCEL);

// Load the Excel template
$TBS->LoadTemplate('demo_template.xml');

// Merge Example 1 (in sheet #1)
$TBS->MergeBlock('book',$books);

// Merge Example 2 (in sheet #2)
$TBS->MergeBlock('tsk1,tsk2',$tasks);
$TBS->MergeBlock('emp',$employees);

$x = 33.69;
// Final merge and download file
$TBS->Show(TBS_EXCEL_DOWNLOAD, 'result.xml');


?>