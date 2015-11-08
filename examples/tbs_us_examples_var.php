<?php

include_once('tbs_class.php');

$amount = 3.55;
$task['monday'] = '<cooking>';

class clsObj {
	var $param = 'hello';
}
$obj = new clsObj;

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_var.htm');
$TBS->Show();

?>