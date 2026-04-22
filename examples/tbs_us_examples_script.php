<?php

include_once('../tbs_class.php');

$test = 'tbs_us_examples_script1.php';

$TBS = new clsTinyButStrong;
$TBS->SetOption('scripts_allowed', true);
$TBS->LoadTemplate('tbs_us_examples_script.htm');
$TBS->Show();

