<?php

if (intval(PHP_VERSION)>=5) {
	include_once('../tbs_class_php5.php');
	//include_once('tbs_class_php5.php');
} else {
	include_once('../tbs_class_php4.php');
}

$ChrOpen = '[';
$ChrClose = ']';

$TBS = new clsTinyButStrong;

$TBS->LoadTemplate('test_tbs_3.6.0.html');

$t1 = f_Timer();
for ($i=1;$i<=10000;$i++) {
	//$Loc =  $TBS->meth_Locator_FindTbs($TBS->Source, 'here', 0, '.');
	//$Loc =  $TBS->meth_Locator_FindTbs($TBS->Source, 'here', 0, '.', $ChrOpen, $ChrClose);
	$Loc =  clsTinyButStrong::meth_Locator_FindTbs($TBS->Source, 'here', 0, '.', $ChrOpen, $ChrClose);
}
$t2 = f_Timer();

$duree = ($t2 - $t1);
echo "durée : $duree<br>\r\n";
echo "loc=".var_export($Loc,true);

// --------------------------------------

function f_Timer() {
// return the currentdate-time in secondes, compatible with PHP 4 and higher
	$x = microtime() ;
	$p = strpos($x,' ') ;
	if ($p===False) {
		$x = '0.0' ;
	} else {
		$x = substr($x,$p+1).substr($x,1,$p) ;
	} ;
	return (float)$x ;
}

?>