<?php

/*
Skrol29, 2010-12-16
http://www.tinybutstrong.com/onlyyou.html
*/

f_InfoStart('file_existe() vs @fopen()');

f_EchoLine('Presentation','u');
echo "This test compares two ways of opening a file that may or may not exist.<br />";

/* --------------
   Speed measures
   -------------- */

f_EchoLine();
f_EchoLine('Speed measures','u');

$b0 = f_BenchThisFct('f_Nothing');

$prm_ok  = array(basename(__FILE__)); 
$prm_err = array('this_file_do_not_exists.txt'); 

$b_fe_ok  = f_BenchThisFct('f_test_file_exists',$prm_ok);
$b_fe_err = f_BenchThisFct('f_test_file_exists',$prm_err);
$b_of_ok  = f_BenchThisFct('f_test_ofile',$prm_ok);
$b_of_err = f_BenchThisFct('f_test_ofile',$prm_err);

/* ---------------
   compare results
   --------------- */

f_EchoLine();
f_EchoLine('Compare results','u');

f_Compare("file_exists() with existing file" , $b_fe_ok, "@fopen() with existing file", $b_of_ok);
f_Compare("file_exists() with non existing file" , $b_fe_err, "@fopen() with non existing file", $b_of_err);

/* ------------
   end
   ------------ */

f_EchoLine();
f_EchoLine('End of tests','u');
$file = 'phpbench_fileexists_vs_fopen.php';
f_InfoEnd('<a href="http://tinybutstrong.svn.sourceforge.net/viewvc/tinybutstrong/trunk/benches/'.$file.'?revision=145&view=markup">Source code of this bench</a>. Created for the <a href="http://www.tinybutstrong.com">TinyButStrong</a> project.',false);
exit;

/* --------------------------------------------
   FUNCTIONS AND CLASSES SPECIFIC TO THIS BENCH
   -------------------------------------------- */

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

/* ---------------------------------
   COMMON FUNCTIONS (version 1.1)
   ---------------------------------*/

function f_Nothing() {
// used to bench a function that does nothing
	$x = false;
	return $x;
}

function f_BenchThisFct($fct, $arg=null, $nbr = 10000) {
// bench a function that takes zero to 5 arguments.
	$x = false;
	if (is_null($arg)) $arg = array();
	$arg_nbr = count($arg);
	$t1 = f_Timer();
	switch ($arg_nbr) {
		case 0: for ($i=0;$i<$nbr;$i++) $x = $fct(); break; // do not use call_user_func_array() or call_user_func() because they get time proportionally to the length of the function's name.
		case 1: for ($i=0;$i<$nbr;$i++) $x = $fct($arg[0]); break;
		case 2: for ($i=0;$i<$nbr;$i++) $x = $fct($arg[0], $arg[1]); break;
		case 3: for ($i=0;$i<$nbr;$i++) $x = $fct($arg[0], $arg[1], $arg[2]); break;
		case 4: for ($i=0;$i<$nbr;$i++) $x = $fct($arg[0], $arg[1], $arg[2], $arg[3]); break;
		case 5: for ($i=0;$i<$nbr;$i++) $x = $fct($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]); break;
		default: exit('ERROR: more that 5 arguments are given to bench function '.$fct.'().');
	}
	$t2 = f_Timer();
	$d = ($t2-$t1);
	$av = $d/$nbr;
	if ($av>=0.1) {
		$av_txt = number_format($av,3,'.',',').' secconds';
	} elseif ($av>=0.001) {
		$av_txt = number_format(1000*$av,3,'.',',').' milli-secconds';
	} elseif ($av>=0.000001) {
		$av_txt = number_format(1000000*$av,3,'.',',').' micro-secconds';
	} else {
		$av_txt = number_format(1000000*$av,12,'.',',').' micro-secconds';
	}
	f_EchoLine("Bench of function '".$fct."': run ".number_format($nbr,0,'.',',')." times, average duration: ".$av_txt.".");
	return $d;
}

function f_Timer() {
// return the currentdate-time in secondes, compatible with PHP 4 and higher
	$x = microtime() ;
	$p = strpos($x,' ') ;
	if ($p===False) {
		$x = '0.0' ;
	} else {
		$x = substr($x,$p+1).substr($x,1,$p) ;
	} ;
	return (float)$x ;
}

function f_EchoLine($txt='',$conv=true) {
// display a line of information
	if ($conv===true) {
		$txt = htmlentities($txt);
	} elseif (is_string($conv)) {
		$txt = '<'.$conv.'>'.htmlentities($txt).'</'.$conv.'>';
	}
	echo $txt."<br />\r";
}

function f_Compare($a_name, $a_val, $b_name, $b_val) {
// display the result of the comparison between two values
	if ($a_val>$b_val) {
		$x_val = $a_val;
		$a_val = $b_val;
		$b_val = $x_val;
		$x_name = $a_name;
		$a_name = $b_name;
		$b_name = $x_name;
	} 
	f_EchoLine( '['.$a_name.'] is '.number_format($b_val/$a_val,2).' time faster than ['.$b_name.'] , that is a reduction of '.number_format(100*($b_val-$a_val)/$b_val,2).'% compared to ['.$b_name.'].' );
}

function f_InfoStart($title) {
// display information at the start of the test	
	global $t_start;
	$t_start = f_Timer();
	
	echo '<!DOCTYPE HTML><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>PHP Benches - '.$title.'</title></head><body>';
	f_EchoLine('<b>PHP Benches:</b> '.htmlentities($title), false);
	f_EchoLine('<b>PHP version:</b> '.PHP_VERSION,false);
	f_EchoLine('<b>OS type:</b> '.PHP_OS.' ('.php_uname('s').')',false);
	f_EchoLine();
	
}

function f_InfoEnd($signature=false,$conv=true) {
// display information at the end of the test	
	global $t_start;
	$t_end = f_Timer();
	f_EchoLine("Total duration: ".number_format($t_end-$t_start,2)." sec.");
	if ($signature!==false) f_EchoLine($signature,$conv);
	echo '</body></html>';
}