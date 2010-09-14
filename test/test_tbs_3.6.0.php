<?php

/*
$x = array('a'=>'coucou', 'b'=>null, 'c'=>'salut');
var_export(isset($x['b']));
exit;
*/
if (intval(PHP_VERSION)>=5) {
	include_once('../tbs_class_php5.php');
} else {
	include_once('../tbs_class_php4.php');
}


$deplace = "essai2";
$x1 = 'e';
$x2 = 'é';

$TBS = new clsTinyButStrong('','','f_test1_');
$TBS->OnLoad = false;
$TBS->OnShow = false;
$TBS->PlugIn(TBS_INSTALL,'clsTestPlugin');

$TBS->LoadTemplate('test_tbs_3.6.0.html');

$TBS->MergeBlock('z', 'num', 5);

$dq_data = array();
$dq_data['1'] = array(array('id'=>29, 'name'=>'George'), array('id'=>30, 'name'=>'Kevin'));
$dq_data[''] = array(array('id'=>31, 'name'=>'Boby'), array('id'=>32, 'name'=>'Steph'));
$dq_data['3'] = array(array('id'=>33, 'name'=>'Julia'), array('id'=>34, 'name'=>'Louis'));
$TBS->MergeBlock('dq', 'array', 'dq_data[%p1%]'); // dynamic query avec "p1="
$TBS->MergeBlock('dr', 'array', 'dq_data[3]'); // dynamic query avec "p1="

// test du paramètre atttrue
$TBS->MergeBlock('att', 'num', 6);

// test bug du GetBlock Source
$bsrc = $TBS->GetBlockSource('w', false, false);

$ErrCount = $TBS->ErrCount;

// test limite de fonction
//$TBS->FctPrefix = 'f_test1_';
$TBS->MergeField('mf1', 'coucou');

$x_list = array('un','deux','trois');
$x_list2 = array(array('un','deux','trois'),array('quatre','cinq','six'));

// test bug du subblock automatique avec valeur null
$sb_data = array();
$sb_data[] = array('nom'=>'Pierre' , 'notes'=>array('A','B','C'));
$sb_data[] = array('nom'=>'Paul'   , 'notes'=>array('D','E','F'));
$sb_data[] = array('nom'=>'Jacques', 'notes'=>null);
$sb_data[] = array('nom'=>'Jacques2');
$sb_data[] = array('nom'=>'Thomas' , 'notes'=>array('J','K','L'));
$TBS->MergeBlock('sb', $sb_data);

$TBS->Show();

// -----------------------------------

class clsTestPlugin {
	function OnInstall() {
		$this->Version = '1.00'; // Version can be displayed using [var..tbs_info] since TBS 3.2.0
		return array('OnCacheField');
	}
	function OnCacheField($BlockName,&$Loc,&$Txt,$PrmProc) {
		echo "* OnCacheField: ".$Loc->FullName."<br>\r\n";
	}
}

function f_test1_onformat($FieldName,&$Value) {
	$Value .= '+1';
} 
function f_test2_onformat($FieldName,&$Value) {
	$Value .= '+2';
} 

?>