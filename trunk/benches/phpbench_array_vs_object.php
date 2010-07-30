<?php

f_InfoStart('Arrays vs Objects');

// memory tests
// ------------

$mem0 = (int) 0;
$mem1 = (int) 0;

$a = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
$i = new clsTest();
$o = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
$name = 'James';
$subname = 'Dean';
$id = 33;

$mem0 = memory_get_usage();
unset($a);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the array: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($i);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the specific object: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($o);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the standard object: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($name); unset($subname); unset($id);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the named variables: ".($mem0-$mem1)." bytes");


// speed tests
// 

f_EchoLine();

/* ---------------------------------
*/

$b0 = f_BechThisFct('f_Nothing');

$b_create_array = f_BechThisFct('f_test_create_array');

$b_create_object_std = f_BechThisFct('f_test_create_object_std');

$b_create_object_spec = f_BechThisFct('f_test_create_object_spec');

$x = f_test_create_array();
$prm = array(&$x); 
$b_read_array = f_BechThisFct('f_test_read_array', $prm);

$x = f_test_create_object_std();
$prm = array(&$x); 
$b_read_object_std = f_BechThisFct('f_test_read_object_any', $prm);

$x = f_test_create_object_spec();
$prm = array(&$x); 
$b_read_object_spec = f_BechThisFct('f_test_read_object_any', $prm);

f_Compare("create standard objet", $b_create_object_std, "create array", $b_create_array);
f_Compare("read standard object",$b_read_object_std, "read array", $b_read_array);
f_Compare("create specific object", $b_create_object_spec, "create standard object", $b_create_object_std);
f_Compare("read specific object", $b_read_object_spec, "read standard object", $b_read_object_std);

// end
f_InfoEnd();
exit;

/* ---------------------------------
   SPECIFIC FUNCTIONS AND CLASSES
   ---------------------------------*/

class clsTest {
	var $name = 'James';
	var $subname = 'Dean';
	var $id = 33;
}

function f_test_create_object_std() {
	$x = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_create_object_spec() {
	$x = new clsTest();
	return $x;
}

function f_test_create_array() {
	$x = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_read_object_any(&$x) {
	$a = $x->name;
	$b = $x->subname;
	$c = $x->id;
	return $a.$b.$c;
}

function f_test_read_array($x) {
	$a = $x['name'];
	$b = $x['subname'];
	$c = $x['id'];
	return $a.$b.$c;
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
	f_EchoLine( '['.$a_name.'] is '.number_format($b_val/$a_val,2).' time faster than ['.$b_name.'] , that is a reduction of -'.number_format(100*($b_val-$a_val)/$b_val,2).'% compared to ['.$b_name.'].' );
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