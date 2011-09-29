<?php

error_reporting(E_ALL & ~E_USER_NOTICE);

// include classes required for unit tests
@require_once(dirname(__FILE__).'/simpletest/unit_tester.php');
@require_once(dirname(__FILE__).'/simpletest/reporter.php');
@require_once(dirname(__FILE__).'/simpletest/mock_objects.php');

// include tbs classes
if (version_compare(PHP_VERSION,'5.0')<0)
	$tbsFileName = dirname(dirname(__FILE__)).'/tbs_class_php4.php';
else
	$tbsFileName = dirname(dirname(__FILE__)).'/tbs_class_php5.php';
@require_once($tbsFileName);
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_html.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_bypage.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_cache.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_mergeonfly.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_navbar.php');
// @require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_ref.php');
// @require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_syntaxes.php');

// other files required for unit tests
require_once(dirname(__FILE__).'/include/TBSUnitTestCase.php');
require_once(dirname(__FILE__).'/include/HtmlCodeCoverageReporter.php');

// include unit test classes
include(dirname(__FILE__).'/testcase/AttTestCase.php');
include(dirname(__FILE__).'/testcase/QuoteTestCase.php');
include(dirname(__FILE__).'/testcase/FrmTestCase.php');
include(dirname(__FILE__).'/testcase/FieldTestCase.php');
include(dirname(__FILE__).'/testcase/BlockTestCase.php');
include(dirname(__FILE__).'/testcase/MiscTestCase.php');

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
$test->addTestCase(new MiscTestCase());
$test->run(new HtmlCodeCoverageReporter(array($tbsFileName, dirname(dirname(__FILE__)).'/plugins/')));

?>