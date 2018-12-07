<?php

include '../tbs_class.php';
include 'myclass.php';

$class = new myclass();
$TBS   = new clsTinyButStrong ;

$TBS->MethodsAllowed = true;
$TBS->LoadTemplate('page.html') ;

// refrence the class in TBS
$TBS->ObjectRef['myclass'] = $class;

// merge a block from a class
$TBS->MergeBlock('peoples',$class, 'people') ;

$TBS->Show() ;
