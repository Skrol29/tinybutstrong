<?php

if (isset($this)) {
  // We are under the TBS Subtemplate Mode => variables are local by default and the TBS object is referenced by variable $this.
	$TBS = &$this;
} else {
  // This sub-script can also be run under the normal mode => its corresponding template will be displayed like a main template.
	include_once('tbs_class.php');
	$TBS = new clsTinyButStrong;
}

global $err_log; // Don't forget that variables are local by default in the Subtemplate Mode.

if (isset($_POST['btn_ok'])) {
  // Imagine we check the login/password...
	$err_log = 1;
}	else {
	$err_log = 0;
}

$TBS->LoadTemplate('tbs_us_examples_subtpl_login.htm');
$TBS->Show() ;  // When calling this method in Subtemplate Mode, the main script won't end, and this merged subtemplated will be inserted into the main template.

?>