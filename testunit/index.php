<?php

/* Versionning
Skrol29, 2010-12-13: rename 'Tiny But Strong' with 'TinyButStrong'
Skrol29, 2010-12-14: deactivate plug-in inclusion because most of them are auto-loaded with TBS
*/

error_reporting(E_ALL & ~E_USER_NOTICE);

// include classes required for unit tests
@require_once(dirname(__FILE__).'/simpletest/unit_tester.php');
@require_once(dirname(__FILE__).'/simpletest/reporter.php');
@require_once(dirname(__FILE__).'/simpletest/mock_objects.php');

// include tbs classes
if (version_compare(PHP_VERSION,'5.0')<0)
	@require_once(dirname(dirname(__FILE__)).'/tbs_class_php4.php');
else
	@require_once(dirname(dirname(__FILE__)).'/tbs_class_php5.php');

/* deactivate plug-in inclusion because most of them are auto-loaded with TBS
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_bypage.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_cache.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_html.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_mergeonfly.php');
@require_once(dirname(dirname(__FILE__)).'/plugins/tbs_plugin_navbar.php');
*/

// other files required for unit tests
require_once(dirname(__FILE__).'/include/TBSUnitTestCase.php');

// include unit test classes
include(dirname(__FILE__).'/testcase/AttTestCase.php');
include(dirname(__FILE__).'/testcase/QuoteTestCase.php');

// launch tests
ini_set("display_errors", "On");
$tbs = new clsTinyButStrong();
$test = new GroupTest('TinyButStrong v'.$tbs->Version.' (with PHP '.PHP_VERSION.')');
$test->addTestCase(new AttTestCase());
$test->addTestCase(new QuoteTestCase());
$test->run(new HtmlReporter());

?>