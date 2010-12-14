<?php

f_InfoStart('file_existe() vs @fopen()');

echo "This test compares two ways of opening a file that may or may not exist.";

// speed tests

f_EchoLine();

/* ---------------------------------
*/

$b0 = f_BechThisFct('f_Nothing');

$prm_ok  = array(basename(__FILE__)); 
$prm_err = array('this_file_do_not_exists.txt'); 

$b_fe_ok  = f_BechThisFct('f_test_file_exists',$prm_ok);
$b_fe_err = f_BechThisFct('f_test_file_exists',$prm_err);
$b_of_ok  = f_BechThisFct('f_test_ofile',$prm_ok);
$b_of_err = f_BechThisFct('f_test_ofile',$prm_err);

f_Compare("file_exists() with existing file" , $b_fe_ok, "@fopen() with existing file", $b_of_ok);
f_Compare("file_exists() with non existing file" , $b_fe_err, "@fopen() with non existing file", $b_of_err);

// end
f_InfoEnd();
exit;

/* ---------------------------------
   SPECIFIC FUNCTIONS AND CLASSES
   ---------------------------------*/

function f_test_file_exists($file) {
	if (file_exists($file)) {
		$x = fopen($file,'r',true);
		fclose($x);
		return true;
	} else {
		return false;
	}
}

function f_test_ofile($file) {
	$x = @fopen($file,'r',true);
	if ($x===false) {
		return false;
	} else {
		fclose($x);
		return true;
	}
}

/* ---------------------------------
   COMMON FUNCTIONS
   ---------------------------------*/

function f_Nothing() {
// used to bench a function that does nothing
	$x = false;
	return $x;
}

function f_BechThisFct($fct, $prm=false, $nbr = 10000) {
// bench a function
	$x = false;
	if ($prm===false) $prm = array();
	$t1 = f_Timer();
	for ($i=0;$i<$nbr;$i++) {
		$x = call_user_func_array($fct, $prm);
	}
	$t2 = f_Timer();
	return ($t2-$t1);
}

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

function f_EchoLine($x='') {
// display a line of information
	echo htmlentities($x)."<br />\r";
}

function f_Compare($a_name, $a_val, $b_name, $b_val) {
// display the result of the comparison between two values
	if ($a_val>$b_val) {
		$x_val = $a_val;
		$a_val = $b_val;
		$b_val = $x_val;
		$x_name = $a_name;
		$a_name = $b_name;
		$b_name = $x_name;
	} 
	f_EchoLine( '['.$a_name.'] is '.number_format($b_val/$a_val,2).' time faster than ['.$b_name.'] , that is a reduction of '.number_format(100*($b_val-$a_val)/$b_val,2).'% compared to ['.$b_name.'].' );
}

function f_InfoStart($Title) {
// display information at the start of the test	
	global $t_start;
	$t_start = f_Timer();
	
	f_EchoLine('PHP Benches: '.$Title);
	f_EchoLine('PHP version: '.PHP_VERSION);
	f_EchoLine('OS type: '.PHP_OS.' ('.php_uname('s').')');
	f_EchoLine();
	
}

function f_InfoEnd() {
// display information at the end of the test	
	global $t_start;
	$t_end = f_Timer();
	f_EchoLine();
	f_EchoLine("End of the test. Duration: ".number_format($t_end-$t_start,2)." sec.");
}