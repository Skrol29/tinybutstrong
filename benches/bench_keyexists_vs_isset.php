<?php

include(dirname(__FILE__).'/include/Benchmark.php');

function f_test_keyexists($prms) {
	return array_key_exists('thekey', $prms);
}

function f_test_isset($prms) {
	return isset($prms['thekey']);
}

$benchmark = new Benchmark('bench isset() Vs array_key_exists()');
$benchmark->setRunningCount(10000);
$params_without  = array('param1'=>'value1', 'name'=>'Paul', 'id='>23); 
$params_with     = array('param1'=>'value1', 'name'=>'Paul', 'thekey'=>'here', 'id='>23); 
$result1 = $benchmark->run('array_key_exists() with existing key', 'f_test_keyexists', array($params_with));
$result2 = $benchmark->run('array_key_exists() with non existing key', 'f_test_keyexists', array($params_without));
$result3 = $benchmark->run('isset() with existing key', 'f_test_isset', array($params_with));
$result4 = $benchmark->run('isset() with non existing key', 'f_test_isset', array($params_without));
$benchmark->compare($result1, $result3);
$benchmark->compare($result2, $result4);
$benchmark->showResults();