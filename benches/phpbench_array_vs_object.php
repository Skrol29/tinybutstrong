<?php

/*
Skrol29, 2010-12-16
http://www.tinybutstrong.com/onlyyou.html
*/

f_InfoStart('Arrays vs Objects');

f_EchoLine('Presentation','u');
echo "This test compares the usage of 4 information storage:
<table border='0' padding='3'>
 <tr><td>array:           </td><td>&nbsp;</td><td>\$x = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);</td></tr>
 <tr><td>specific object: </td><td>&nbsp;</td><td>\$x = new clsTest; class clsTest {var \$name = 'James'; var \$subname = 'Dean'; var \$id = 33;}</td></tr>
 <tr><td>standard object: </td><td>&nbsp;</td><td>\$x = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);</td></tr>
 <tr><td>named variables: </td><td>&nbsp;</td><td>\$name = 'James'; \$subname = 'Dean'; \$id = 33;</td></tr>
</table>";

/* ------------
   memory measures
   ------------ */
f_EchoLine();
f_EchoLine('Memory measures','u');
   
$mem0 = (int) 0;
$mem1 = (int) 0;

$a = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
$i = new clsTest();
$o = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
$name = 'James';
$subname = 'Dean';
$id = 33;

$mem0 = memory_get_usage();
unset($a);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the array: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($i);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the specific object: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($o);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the standard object: ".($mem0-$mem1)." bytes");

$mem0 = memory_get_usage();
unset($name); unset($subname); unset($id);
$mem1 = memory_get_usage();
f_EchoLine("Memory size for the named variables: ".($mem0-$mem1)." bytes");


/* --------------
   Speed measures
   -------------- */

f_EchoLine();
f_EchoLine('Speed measures','u');

$b0 = f_BechThisFct('f_Nothing');

$b_create_array = f_BechThisFct('f_test_create_array');

$b_create_object_std = f_BechThisFct('f_test_create_object_std');

$b_create_object_spec = f_BechThisFct('f_test_create_object_spec');

$x = f_test_create_array();
$prm = array(&$x); 
$b_read_array = f_BechThisFct('f_test_read_array', $prm);

$x = f_test_create_object_std();
$prm = array(&$x); 
$b_read_object_std = f_BechThisFct('f_test_read_object_any', $prm);

$x = f_test_create_object_spec();
$prm = array(&$x); 
$b_read_object_spec = f_BechThisFct('f_test_read_object_any', $prm);

/* ---------------
   compare results
   --------------- */

f_EchoLine();
f_EchoLine('Compare results','u');

f_Compare("create standard objet", $b_create_object_std, "create array", $b_create_array);
f_Compare("read standard object",$b_read_object_std, "read array", $b_read_array);
f_Compare("create specific object", $b_create_object_spec, "create standard object", $b_create_object_std);
f_Compare("read specific object", $b_read_object_spec, "read standard object", $b_read_object_std);

/* ------------
   end
   ------------ */

f_EchoLine();
f_EchoLine('End of tests','u');
$file = 'phpbench_array_vs_object.php';
f_InfoEnd('<a href="http://tinybutstrong.svn.sourceforge.net/viewvc/tinybutstrong/trunk/benches/'.$file.'?revision=145&view=markup">Source code of this bench</a>. Created for the <a href="http://www.tinybutstrong.com">TinyButStrong</a> project.',false);
exit;

/* --------------------------------------------
   FUNCTIONS AND CLASSES SPECIFIC TO THIS BENCH
   -------------------------------------------- */

class clsTest {
	var $name = 'James';
	var $subname = 'Dean';
	var $id = 33;
}

function f_test_create_object_std() {
	$x = (object) array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_create_object_spec() {
	$x = new clsTest();
	return $x;
}

function f_test_create_array() {
	$x = array('name' => 'James', 'subname' => 'Dean', 'id' => 33);
	return $x;
}

function f_test_read_object_any(&$x) {
	$a = $x->name;
	$b = $x->subname;
	$c = $x->id;
	return $a.$b.$c;
}

function f_test_read_array($x) {
	$a = $x['name'];
	$b = $x['subname'];
	$c = $x['id'];
	return $a.$b.$c;
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