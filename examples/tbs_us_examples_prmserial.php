<?php

include_once('tbs_class.php');

$recset[] = array('title'=>'I will love you'  , 'rank'=>'A');
$recset[] = array('title'=>'Tender thender'   , 'rank'=>'B');
$recset[] = array('title'=>'I got you babe'   , 'rank'=>'C');
$recset[] = array('title'=>'Only with you'    , 'rank'=>'D');
$recset[] = array('title'=>'Love me tender'   , 'rank'=>'E');
$recset[] = array('title'=>'Wait for me'      , 'rank'=>'F');
$recset[] = array('title'=>'Happy pop'        , 'rank'=>'G');
$recset[] = array('title'=>'Kiss me like that', 'rank'=>'H');
$recset[] = array('title'=>'Love me so'       , 'rank'=>'I');
$recset[] = array('title'=>'Us, you and I'    , 'rank'=>'J');

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_prmserial.htm');
$TBS->MergeBlock('bx',$recset);
$TBS->MergeBlock('bz',$recset);
$TBS->Show();

?>