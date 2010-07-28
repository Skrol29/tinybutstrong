<?php

f_EchoLine("PHP Benches: Arrays vs Objects");
f_EchoLine("PHP version: ".PHP_VERSION);
f_EchoLine("OS type: ".PHP_OS." (".php_uname('s').")");

class clsTest {
	var $name = 'James';
	var $subname = 'Dean';
	var $id = 33;
}

// memory tests
// ------------

f_EchoLine();

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

$b_create_array = f_BechThisFct('f_test_create_array') - $b0;

$b_create_object = f_BechThisFct('f_test_create_object') - $b0;

$b_create_object2 = f_BechThisFct('f_test_create_object2') - $b0;

$x = f_test_create_array();
$prm = array(&$x); 
$b_read_array = f_BechThisFct('f_test_read_array', $prm) - $b0;

$x = f_test_create_object();
$prm = array(&$x); 
$b_read_object = f_BechThisFct('f_test_read_object', $prm) - $b0;

$x = f_test_create_object2();
$prm = array(&$x); 
$b_read_object2 = f_BechThisFct('f_test_read_object', $prm) - $b0;

f_EchoLine("(create std object) / (create array) = ".f_Rate($b_create_object,$b_create_array));
f_EchoLine("(read std object) / (read array) = ".f_Rate($b_read_object,$b_read_array));
f_EchoLine("(create spec object) / (create std  object) = ".f_Rate($b_create_object2,$b_create_object));
f_EchoLine("(read spec object) / (read std object) = ".f_Rate($b_read_object2,$b_read_object));
exit;

/* ---------------------------------
*/

function f_test_create_object() {
	$x = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_create_object2() {
	$x = new clsTest();
	return $x;
}

function f_test_create_array() {
	$x = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_read_object(&$x) {
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
*/

function f_Nothing() {
	$x = false;
	return $x;
}

function f_BechThisFct($fct, $prm=false, $nbr = 10000) {
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
// compatible with PHP 4 and higher
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
	echo htmlentities($x)."<br />\r";
}

function f_Rate($a, $b) {
	return number_format(100*$a/$b,2)."%";
}