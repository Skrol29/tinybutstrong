<?php

/*
Skrol29, 2010-12-16
http://www.tinybutstrong.com/onlyyou.html
*/

f_InfoStart('Function vs Method');

f_EchoLine('Presentation','u');
echo "
This test compares the time execution between a function, a method of an instanciated object, and a static method.<br />
";

/* --------------
   Speed measures
   -------------- */

f_EchoLine();
f_EchoLine('Speed measures','u');

$obj = new clsTest();

$x = 29;
$prm = array($x);
$prm_obj = array(&$obj, $x);

$b0             = f_BechThisFct('f_Nothing');
$b_Function     = f_BechThisFct('f_function_std', $prm);
$b_Method       = f_BechThisFct('f_method_std', $prm_obj);
$b_StaticMethod = f_BechThisFct('f_method_static', $prm);
/* ---------------
   compare results
   --------------- */

f_EchoLine();
f_EchoLine('Compare results','u');

f_Compare("Function", $b_Function, "Method", $b_Method);
f_Compare("Function", $b_Function, "StaticMethod", $b_StaticMethod);
f_Compare("Method", $b_Method, "StaticMethod", $b_StaticMethod);

/* ------------
   end
   ------------ */

f_EchoLine();
f_EchoLine('End of tests','u');
f_InfoEnd('<a href="http://www.tinybutstrong.com">http://www.tinybutstrong.com</a>',false);
exit;

/* --------------------------------------------
   FUNCTIONS AND CLASSES SPECIFIC TO THIS BENCH
   -------------------------------------------- */

function f_function_std($x) {
	$y = f_function($x);
	return $y;
}   

function f_method_std(&$obj, $x) {
	$y = $obj->method($x);
	return $y;
}

function f_method_static($x) {
	$y = clsTest::static_method($x);
	return $y;
}

function f_function($x) {
	// a simple function
	return ($x+1);
}

class clsTest {
	function method($x) {
		// a simple method
		return ($x+1);
	}	
	static function static_method($x) {
		// a simple method
		return ($x+1);
	}	
}



/* ---------------------------------
   COMMON FUNCTIONS (version 1.0)
   ---------------------------------*/

function f_Nothing() {
// used to bench a function that does nothing
	$x = false;
	return $x;
}

function f_BechThisFct($fct, $prm=false, $nbr = 10000) {
// bench a function
	$x = false;
	if ($prm===false) $prm = array();
	$t1 = f_Timer();
	for ($i=0;$i<$nbr;$i++) {
		$x = call_user_func_array($fct, $prm);
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
