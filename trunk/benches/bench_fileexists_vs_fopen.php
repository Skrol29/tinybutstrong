<?php

include(dirname(__FILE__).'/include/Benchmark.php');

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

$benchmark = new Benchmark('bench file_exists() Vs fopen()');
$benchmark->setRunningCount(10000);
$params_ok  = array(basename(__FILE__)); 
$params_err = array('this_file_do_not_exists.txt'); 
$result1 = $benchmark->run('file_exists() with existing file', 'f_test_file_exists', $params_ok);
$result2 = $benchmark->run('file_exists() with non existing file', 'f_test_file_exists', $params_err);
$result3 = $benchmark->run('fopen() with existing file', 'f_test_ofile', $params_ok);
$result4 = $benchmark->run('fopen() with non existing file', 'f_test_ofile', $params_err);
$benchmark->compare($result1, $result2);
$benchmark->compare($result3, $result4);
$benchmark->compare($result1, $result3);
$benchmark->compare($result2, $result4);
$benchmark->showResults();

?>