<?php

error_reporting(E_ALL & ~E_USER_NOTICE & ~E_STRICT);
ini_set("display_errors", "On");
set_time_limit(0);
//ini_set('memory_limit', '256M');


$dir_testu = dirname(__FILE__);
$dir_tbs = dirname($dir_testu);

// include classes required for unit tests
// "@" is in order to avoid Deprecated warnings
@require_once($dir_testu.'/simpletest/unit_tester.php');
require_once($dir_testu.'/simpletest/reporter.php');
@require_once($dir_testu.'/simpletest/mock_objects.php');

// include tbs classes
if (version_compare(PHP_VERSION,'5.0')<0) {
	$tbsFileName = $dir_tbs.'/tbs_class_php4.php';
} else {
	$tbsFileName = $dir_tbs.'/tbs_class.php';
}
require_once($tbsFileName);

// other files required for unit tests
require_once($dir_testu.'/include/TBSUnitTestCase.php');
require_once($dir_testu.'/include/HtmlCodeCoverageReporter.php');

// include unit test classes
include($dir_testu.'/testcase/AttTestCase.php');
include($dir_testu.'/testcase/QuoteTestCase.php');
include($dir_testu.'/testcase/FrmTestCase.php');
include($dir_testu.'/testcase/StrconvTestCase.php');
include($dir_testu.'/testcase/FieldTestCase.php');
include($dir_testu.'/testcase/BlockTestCase.php');
include($dir_testu.'/testcase/MiscTestCase.php');
include($dir_testu.'/testcase/SubTplTestCase.php');

// launch tests

$tbs = new clsTinyButStrong();
$test = new GroupTest('TinyButStrong v'.$tbs->Version.' (with PHP '.PHP_VERSION.')');
$x1 = new FieldTestCase();   $test->addTestCase($x1);
$x2 = new BlockTestCase();   $test->addTestCase($x2);
$x3 = new AttTestCase();     $test->addTestCase($x3);
$x4 = new QuoteTestCase();   $test->addTestCase($x4);
$x5 = new FrmTestCase();     $test->addTestCase($x5);
$x6 = new StrconvTestCase(); $test->addTestCase($x6);
$x7 = new MiscTestCase();    $test->addTestCase($x7);
$x8 = new SubTplTestCase();  $test->addTestCase($x8);

$xx = new HtmlCodeCoverageReporter(array($tbsFileName, $dir_tbs.'/plugins/'));
$test->run($xx);

