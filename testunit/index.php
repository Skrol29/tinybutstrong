<?php

error_reporting(E_ALL);
ini_set("display_errors", "On");
set_time_limit(0);
//ini_set('memory_limit', '256M');


$dir_testu = dirname(__FILE__);
$dir_tbs = dirname($dir_testu);
$dir_plugins = $dir_tbs . '/plugins';
chdir($dir_testu);

if (!file_exists($dir_plugins)) {
    $dir_plugins = dirname($dir_tbs) . '/tbs_plugins';
}

// include tbs classes
if (version_compare(PHP_VERSION, '5.0') < 0) {
    $tbsFileName = $dir_tbs . '/tbs_class_php4.php';
} else {
    $tbsFileName = $dir_tbs . '/tbs_class.php';
}

// include classes required for unit tests
if (version_compare(PHP_VERSION, '5.4') < 0) {
    // "@" is in order to avoid Deprecated warnings
   @require_once($dir_testu . '/simpletest/simpleTest.php');
   @require_once($dir_testu . '/simpletest/unit_tester.php');
    require_once($dir_testu . '/simpletest/reporter.php');
   @require_once($dir_testu . '/simpletest/mock_objects.php');
} else {
    require_once $dir_tbs . '/vendor/autoload.php';
}

require_once($tbsFileName);

// other files required for unit tests
require_once($dir_testu . '/include/TBSUnitTestCase.php');

if (PHP_SAPI === 'cli') { // Text output
    require_once($dir_testu . '/include/TextCoverageReporter.php');
    $reporter = new TextCoverageReporter();
} else {                  // HTML output
    require_once($dir_testu . '/include/HtmlCodeCoverageReporter.php');
    $reporter = new HtmlCodeCoverageReporter(array($tbsFileName, $dir_plugins . DIRECTORY_SEPARATOR));
}

// include unit test classes
include($dir_testu . '/testcase/AttTestCase.php');
include($dir_testu . '/testcase/QuoteTestCase.php');
include($dir_testu . '/testcase/FrmTestCase.php');
include($dir_testu . '/testcase/StrconvTestCase.php');
include($dir_testu . '/testcase/FieldTestCase.php');
include($dir_testu . '/testcase/BlockTestCase.php');
include($dir_testu . '/testcase/MiscTestCase.php');
include($dir_testu . '/testcase/SubTplTestCase.php');
include($dir_testu . '/testcase/SubnameTestCase.php');

// launch tests
$SimpleTest = new SimpleTest();
$tbs = new clsTinyButStrong();
$test = new TestSuite('TinyButStrong v' . $tbs->Version . ' (with PHP ' . PHP_VERSION . ', simpleTest ' . $SimpleTest->getVersion() . ')');

$test->add(new FieldTestCase());
$test->add(new BlockTestCase());
$test->add(new AttTestCase());
$test->add(new QuoteTestCase());
$test->add(new FrmTestCase());
$test->add(new StrconvTestCase());
$test->add(new MiscTestCase());
$test->add(new SubTplTestCase());
$test->add(new SubnameTestCase());

$test->run($reporter);
