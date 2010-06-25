<?php

/* This script automatically updates tbssql_* files 

*/
// name of special files
$model = (isset($_GET['model'])) ? $_GET['model']  : 'base';
$model = 'tbssql_'.$model.'.php';
if (!file_exists($model)) exit("The model file '$model' is not found.");
$script = basename($_SERVER['SCRIPT_NAME']);

// retreive the part of the code to duplicate
$code = file_get_contents($model);
$p = f_FoundPos($code);
if ($p===false) exit("Common part not found in the model file '$model'.");
$code = substr($code, 0, $p);

// loop for all files in the current directory
$dir = opendir('.');
while ($file = readdir($dir)) {
	if ( ($file!==$model) && ($file!==$script) && (substr($file,0,7)==='tbssql_') && (substr($file,-4)==='.php') ) {
		$source = file_get_contents($file);
		$p = f_FoundPos($source);
		if ($p===false) {
			echo "* file '$file': common part not found in this file, no update applied<br>\r\n";
		} else {
			$x = substr($source, 0, $p);
			if ($x===$code) {
				echo "* file '$file': already up to date<br>\r\n";
			} else {
				$source = $code.substr($source, $p);
				file_put_contents($file, $source);
				echo "* file '$file': updated<br>\r\n";
			}
		}
	}
}
closedir($dir);		


function f_FoundPos($Txt) {
	$p = strpos($Txt, 'function _Dbs_');
	if ($p===false) return false;
	$p = strrpos(substr($Txt,0,$p), '}');
	if ($p===false) return false;
	return ($p+1);
}	