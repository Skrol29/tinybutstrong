<?php

include_once('tbs_class.php');
include_once('tbs_plugin_html.php'); // Plug-in for selecting HTML items.

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_form.htm');

$typelist = array('<other>'=>'-','Mister'=>'Mr','Madame'=>'Mme','Missis'=>'Ms'); 
$TBS->MergeBlock('typeblk',$typelist) ; 

$msg_err = '';
$msg_ok = '';
if (!isset($_POST)) $_POST=&$HTTP_POST_VARS;
if (!isset($_POST['x_type'])) { 
	$x_type = '-'; 
	$x_name = ''; 
	$x_subname = ''; 
	$msg_ok = 'Enter your information and click on [Validate].'; 
} else {
	$msg_text = '';
	$msg_body = array();
	$x_type = $_POST['x_type'];
	$x_name = $_POST['x_name'];
	$x_subname = $_POST['x_subname'] ; 
	if ( ($msg_err==='') && (trim($x_type)=='-') )   $msg_err = 'Please enter your gender.'; 
	if ( ($msg_err==='') && (trim($x_name)=='') )    $msg_err = 'Please enter your name.'; 
	if ( ($msg_err==='') && (trim($x_subname)=='') ) $msg_err = 'Please enter your subname.'; 
	if ($msg_err==='') {
		$msg_ok = 'Thank you.'; 
	}
}

$TBS->Show();

?>