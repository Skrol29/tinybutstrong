<?php

error_reporting(E_ALL & ~E_USER_NOTICE & ~E_STRICT);

$dir_testu = dirname(__FILE__);
$dir_tbs = dirname($dir_testu);
$dir_plugins = $dir_tbs . '/plugins';
if (!file_exists($dir_plugins)) {
	$dir_plugins = dirname($dir_tbs) . '/tbs_plugins';
}
if (!file_exists($dir_plugins)) {
	exit("Plug-ins directory not found. Abort.");
}

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
require_once($dir_plugins.'/tbs_plugin_html.php');
require_once($dir_plugins.'/tbs_plugin_bypage.php');
require_once($dir_plugins.'/tbs_plugin_cache.php');
require_once($dir_plugins.'/tbs_plugin_mergeonfly.php');
require_once($dir_plugins.'/tbs_plugin_navbar.php');
// @require_once($dir_plugins.'/tbs_plugin_ref.php');
// @require_once($dir_plugins.'/tbs_plugin_syntaxes.php');

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
ini_set("display_errors", "On");
set_time_limit(0);
$tbs = new clsTinyButStrong();
$test = new GroupTest('TinyButStrong v'.$tbs->Version.' (with PHP '.PHP_VERSION.')');
$test->addTestCase(new FieldTestCase());
$test->addTestCase(new BlockTestCase());
$test->addTestCase(new AttTestCase());
$test->addTestCase(new QuoteTestCase());
$test->addTestCase(new FrmTestCase());
$test->addTestCase(new StrconvTestCase());
$test->addTestCase(new MiscTestCase());
$test->addTestCase(new SubTplTestCase());
$test->run(new HtmlCodeCoverageReporter(array($tbsFileName, $dir_tbs.'/plugins/')));

