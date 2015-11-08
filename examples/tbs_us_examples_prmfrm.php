<?php

include_once('tbs_class.php');

$amount = 1023.2568;
$amount2 = -255.4893;
$rate = 0.751897;

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_prmfrm.htm');
$TBS->Show();

?>