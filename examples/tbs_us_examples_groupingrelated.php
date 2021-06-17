<?php

include_once('../tbs_class.php');

$result[0]  = array('country'=>'United States', 'winner'=>'Bob',        'score'=>100);
$result[1]  = array('country'=>'United States', 'winner'=>'Julia',      'score'=>99);
$result[2]  = array('country'=>'United States', 'winner'=>'Mark',       'score'=>78);
$result[3]  = array('country'=>'United States', 'winner'=>'Stanley',    'score'=>110);
$result[4]  = array('country'=>'United States', 'winner'=>'Robert',     'score'=>109);
$result[5]  = array('country'=>'Spain',         'winner'=>'Jose',       'score'=>250);
$result[6]  = array('country'=>'France',        'winner'=>'Jean',       'score'=>210);
$result[7]  = array('country'=>'France',        'winner'=>'Gaël',       'score'=>120);
$result[8]  = array('country'=>'France',        'winner'=>'Emmanuelle', 'score'=>260);
$result[9]  = array('country'=>'France',        'winner'=>'Louis',      'score'=>240);
$result[10] = array('country'=>'France',        'winner'=>'Jaques',     'score'=>200);

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_groupingrelated.htm');
$TBS->MergeBlock('blk_res',$result);
$TBS->Show();

