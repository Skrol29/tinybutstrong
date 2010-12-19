<?php

include(dirname(__FILE__).'/include/Benchmark.php');

function f_function_normal(&$obj, $x) {
	$y = f_function($x);
	return $y;
}   

function f_method_normal(&$obj, $x) {
	$y = $obj->method($x);
	return $y;
}

function f_method_static(&$obj, $x) {
	$y = clsTest::static_method($x);
	return $y;
}

function f_method_static_as_normal(&$obj, $x) {
	$y = $obj->static_method($x);
	return $y;
}

function f_function($x) {
	// a simple function
	return ($x+1);
}

class clsTest {
	function method($x) {
		// a simple method
		return ($x+1);
	}	
	static function static_method($x) {
		// a simple method
		return ($x+1);
	}	
}

$obj = new clsTest();
$x = 29;
$params = array(&$obj, $x);

$benchmark = new Benchmark('bench function Vs method');
$benchmark->setRunningCount(10000);
$result1 = $benchmark->run('global function', 'f_function_normal', $params);
$result2 = $benchmark->run('object method', 'f_method_normal', $params);
$result3 = $benchmark->run('static object method', 'f_method_static', $params);
$result4 = $benchmark->run('static object method as normal', 'f_method_static_as_normal', $params);
$benchmark->compare($result1, $result2);
$benchmark->compare($result1, $result3);
$benchmark->compare($result1, $result4);
$benchmark->compare($result2, $result3);
$benchmark->compare($result3, $result4);
$benchmark->showResults();

?>