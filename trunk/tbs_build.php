<?php

/*

While PHP 5 is widely to be preferred than PHP 4, lot of web sites unfortunately still work on PHP 4.
So TinyButStrong continue to be developed for both PHP 4 and PHP 5, and this is not a big effort.
The development class has code that work only for PHP 5 plus some specific fixes for PHP 4.
This development class can then be converted into a pure PHP 4 file or a pure PHP 5 file.
The tool 'tbs_build.php' does make this conversion.

Skrol29, 2010-05-27
*/

if (!isset($_GET['source'])) exit("Enter the name of a TBS development file in argument 'source' in the URL. Usually: source=tbs_class_dev.php");

$Src = basename($_GET['source']);
if (!file_exists($Src)) exit("File '".$Src."' is not found.");

$SrcTxt = file_get_contents($Src);

// Check TBS version consistency
echo "TBS version consistency:<br> \r\n";
$va = f_TextBetween($SrcTxt, 'Version  : ', ' ');
if ($va===false) exit("Check TBS version consistency: TBS version not found in the header");
$vb = f_TextBetween($SrcTxt, '$Version = \'', '\'');
if ($vb===false) exit("Check TBS version consistency: TBS version not found in the property");
if ($va!==$vb) exit("Check TBS version consistency: $va is mentioned in the header while $vb is mentioned in the property.");
echo "OK version $va<br> \r\n";
echo "<br> \r\n";

if (substr_count($Src, '_dev.php')>0) {
	$Dst4 = str_replace('_dev.php','_php4.php',$Src);
	$Dst5 = str_replace('_dev.php','_php5.php',$Src);
} else {
	$Dst4 = str_replace('.php','_php4.php',$Src);
	$Dst5 = str_replace('.php','_php5.php',$Src);
}

if (($Dst4==$Src) || ($Dst5==$Src)) exit("File name '".$Src."' is not compliant.");

echo "The developpement file <b>".$Src."</b> will be converted into PHP 4 and PHP 5 files.<br> \r\n";

// Conversion in PHP 4
echo "<br> \r\n";
echo "Conversion into PHP 4:<br> \r\n";
$Txt = $SrcTxt;
$Ok = true;
$Ok = $Ok && f_Replace($Txt, 'public function ', 'function ', 'Change "public function"');
$Ok = $Ok && f_Replace($Txt, 'static function ', 'function ', 'Change "static function"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof Iterator)', '(is_a($SrcId,\'Iterator\'))', 'Change "instanceof Iterator"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof ArrayObject)', '(is_a($SrcId,\'ArrayObject\'))', 'Change "instanceof ArrayObject"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof IteratorAggregate)', '(is_a($SrcId,\'IteratorAggregate\'))', 'Change "instanceof IteratorAggregate"');
if ($Ok) {
	f_Save($Dst4, $Txt);
	echo "Conversion complete and saved into file <b>".$Dst4."</b><br> \r\n";
} else {
	echo "Conversion with an unexepected result. No file change applied in any file.<br> \r\n";
}

// Conversion in PHP 5
echo "<br> \r\n";
echo "Conversion into PHP 5:<br> \r\n";
$Txt = $SrcTxt;
$Ok = true;
$Ok = $Ok && f_Replace($Txt, 'if (PHP_VERSION===\'4.4.1\') {$this->RecSet = $this->SrcId;} else {$this->RecSet = &$this->SrcId;} // bad bug in PHP 4.4.1', '$this->RecSet = &$this->SrcId;', 'Delete bug 1 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, 'if (PHP_VERSION===\'4.4.1\') {$this->RecSet = $Query;} else {$this->RecSet = &$Query;}', '$this->RecSet = &$Query;', 'Delete bug 2 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, 'if ((PHP_VERSION===\'4.4.1\') and is_array($GLOBALS[$Item0])) {$Var = $GLOBALS[$Item0];} else {$Var = &$GLOBALS[$Item0];}', '$Var = &$GLOBALS[$Item0];', 'Delete bug 3 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, '$this->CurrRec = @pg_fetch_array($this->RecSet,$this->RecNum,PGSQL_ASSOC); // warning comes when no record left.', '$this->CurrRec = pg_fetch_assoc($this->RecSet);', 'Delete a fix for PostgreSQL on PHP<4.1.0');
$Ok = $Ok && f_Replace($Txt, "\n".'	var $', "\n".'	public $', 'Replace var declarations with public (1)');
$Ok = $Ok && f_Replace($Txt, "\n".'var $', "\n".'public $', 'Replace var declarations with public (2)');
$Ok = $Ok && f_Replace($Txt, ' = &new ', ' = new ', ' &new replaced with new');
$Ok = $Ok && f_Replace($Txt, ' for PHP 4', ' for PHP 5', 'version: for PHP 5');
$Ok = $Ok && f_Replace($Txt, 'if (version_compare(PHP_VERSION,\'4.0.6\')<0) echo \'<br><b>TinyButStrong Error</b> (PHP Version Check) : Your PHP version is \'.PHP_VERSION.\' while TinyButStrong needs PHP version 4.0.6 or higher.\';'."\n".'if (!is_callable(\'array_key_exists\')) {'."\n".'	function array_key_exists (&$key,&$array) {return key_exists($key,$array);}'."\n".'}'."\n".'if (!is_callable(\'property_exists\')) {'."\n".'	function property_exists(&$obj,$prop) {return true;}'."\n".'}', 'if (version_compare(PHP_VERSION,\'5.0\')<0) echo \'<br><b>TinyButStrong Error</b> (PHP Version Check) : Your PHP version is \'.PHP_VERSION.\' while TinyButStrong needs PHP version 5.0 or higher. You should try with TinyButStrong Edition for PHP 4.\';', 'check PHP version');

if ($Ok) {
	f_Save($Dst5, $Txt);
	echo "Conversion complete and saved into file <b>".$Dst5."</b><br> \r\n";
} else {
	echo "Conversion with an unexepected result. No file change applied in any file.<br> \r\n";
}

exit;

function f_Replace(&$Txt, $ReplWhat, $ReplWith, $Msg) {
	
	$NbrBefore = substr_count($Txt, $ReplWhat);
	$Txt = str_replace($ReplWhat, $ReplWith, $Txt);
	$NbrAfter = substr_count($Txt, $ReplWhat);
	if ($NbrBefore==0) {
		$ResMsg = '<span style="color: purple; font-style: italic;">no items found</span>';
		$Ok = false;
	} elseif ($NbrAfter>0) {
		$ResMsg = '<span style="color: red; font-weight: bold;">ERROR</span>. '.$NbrBefore.' founds before, '.$NbrAfter.' found after.';
		$Ok = false;
	} else {
		$ResMsg = '<span style="color: green; font-weight: bold;">OK</span>. '.$NbrBefore.' founds before, '.$NbrAfter.' found after.';
		$Ok = true;
	}
	
	echo '- '.$Msg.': '.$ResMsg."<br> \r\n";
	
	return $Ok;
	
}

function f_Save($Dst, $Txt) {
	// does the same as file_put_contents()
	$f = fopen($Dst, 'w');
	fwrite($f, $Txt);
	fclose($f);
}

function f_TextBetween($Txt, $str1, $str2) {
	$p1 = strpos($Txt, $str1);
	if ($p1===false) return false;
	$p1 = $p1+strlen($str1);
	$p2 = strpos($Txt, $str2, $p1);
	if ($p2===false) return false;
	return substr($Txt, $p1, $p2-$p1 );
}