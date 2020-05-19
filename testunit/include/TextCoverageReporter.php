<?php

class TextCoverageReporter extends TextReporter {

    var $code_coverage = array();
    var $character_set = 'utf-8';
    var $time = 0;

    function cleanDirecory($path) {
        return str_replace("/", DIRECTORY_SEPARATOR, str_replace("\\", DIRECTORY_SEPARATOR, $path));
    }

    function getMicrotime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Paints the start of a group test. Will also paint
     * the page header and footer if this is the
     * first test. Will stash the size if the first start.
     * @param string $test_name   Name of test that is starting.
     * @param integer $size       Number of test cases starting.
     */
    function paintGroupStart($test_name, $size) {
        $this->time = $this->getMicrotime();
        parent::paintGroupStart($test_name, $size);
        if (extension_loaded('xdebug'))
            xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    }

    /**
     * Paints the end of a group test. Will paint the page
     * footer if the stack of tests has unwound.
     * @param string $test_name   Name of test that is ending.
     * @param integer $progress   Number of test cases ending.
     */
    function paintGroupEnd($test_name) {
        if (extension_loaded('xdebug')) {
            $this->code_coverage = xdebug_get_code_coverage();
            xdebug_stop_code_coverage();
            ksort($this->code_coverage);
        }
        parent::paintGroupEnd($test_name);
    }

    /**
     * Paints the end of the test with a summary of the passes and failures.
     * @param string $test_name        Name class of test.
     */
    function paintFooter($test_name) {
        print "\n" . $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:";
        print "\n   " . $this->getPassCount() . " passes, ";
        print $this->getFailCount() . " fails and ";
        print $this->getExceptionCount() . " exceptions.";

        $totalTime = $this->getMicrotime() - $this->time;

        print "\nExecution time: ".number_format($totalTime * 1000, 0, '.', ' ')." ms\n";
    }
}
