<?php

include(dirname(__FILE__).'/include/Benchmark.php');

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

function f_test_create_array_declarative() {
	$x = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_create_array_iterative() {
	$x = array();
	$x['name'] = 'James';
	$x['subname'] = 'Dean';
	$x['id'] = 33;
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

$benchmark = new Benchmark('bench array Vs object');
$benchmark->setRunningCount(10000);
$result1 = $benchmark->run('create object from array', 'f_test_create_object_std');
$result2 = $benchmark->run('instanciate a new object', 'f_test_create_object_spec');
$result3 = $benchmark->run('create array', 'f_test_create_array_declarative');
$result4 = $benchmark->run('fill array', 'f_test_create_array_iterative');
$x = f_test_create_object_std();
$result5 = $benchmark->run('read object created from array', 'f_test_read_object_any', array(&$x));
$x = f_test_create_object_spec();
$result6 = $benchmark->run('read instanciated object', 'f_test_read_object_any', array(&$x));
$x = f_test_create_array_declarative();
$result7 = $benchmark->run('read array', 'f_test_read_array', array(&$x));
$benchmark->compare($result1, $result2);
$benchmark->compare($result3, $result4);
$benchmark->compare($result1, $result3);
$benchmark->compare($result6, $result7);
$benchmark->showResults();

?>