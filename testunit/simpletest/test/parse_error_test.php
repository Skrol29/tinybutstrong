<?php
// $Id: parse_error_test.php,v 1.1 2009-06-21 17:26:52 fabrice Exp $
require_once('../unit_tester.php');
require_once('../reporter.php');

$test = new TestSuite('This should fail');
$test->addFile('test_with_parse_error.php');
$test->run(new HtmlReporter());
?>