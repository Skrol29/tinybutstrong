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
$tbsFileName = $dir_tbs.'/tbs_class.php';
require_once($tbsFileName);

// other files required for unit tests
require_once($dir_testu.'/include/TBSUnitTestCase.php');
require_once($dir_testu.'/include/HtmlCodeCoverageReporter.php');

// include unit test classes
include($dir_testu.'/testcase/AttTestCase.php');
include($dir_testu.'/testcase/QuoteTestCase.php');
include($dir_testu.'/testcase/FrmTestCase.php');
include($dir_testu.'/testcase/StrconvTestCase.php');
include($dir_testu.'/testcase/OpeTestCase.php');
include($dir_testu.'/testcase/FieldTestCase.php');
include($dir_testu.'/testcase/BlockTestCase.php');
include($dir_testu.'/testcase/BlockGrpTestCase.php');
include($dir_testu.'/testcase/MiscTestCase.php');
include($dir_testu.'/testcase/SubTplTestCase.php');
include($dir_testu.'/testcase/SubnameTestCase.php');
include($dir_testu.'/testcase/PluginsTestCase.php');

// launch tests

$tbs = new clsTinyButStrong();

$bit = (PHP_INT_SIZE <= 4) ? '32' : '64' ;
$title = "TinyButStrong v" . $tbs->Version . " (with PHP version " . PHP_VERSION . " , " . $bit . "-bits)";
$test = new GroupTest($title);

$test->addTestCase(new FieldTestCase());
$test->addTestCase(new BlockTestCase());
$test->addTestCase(new BlockGrpTestCase());
$test->addTestCase(new AttTestCase());
$test->addTestCase(new QuoteTestCase());
$test->addTestCase(new FrmTestCase());
$test->addTestCase(new OpeTestCase());
$test->addTestCase(new StrconvTestCase());
$test->addTestCase(new MiscTestCase());
$test->addTestCase(new SubTplTestCase());
$test->addTestCase(new SubnameTestCase());
$test->addTestCase(new PluginsTestCase());

$test->run(new HtmlCodeCoverageReporter(array($tbsFileName, $dir_tbs.'/plugins/')));

