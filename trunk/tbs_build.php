<?php

/*

While PHP 5 is widely to be preferred than PHP 4, lot of web sites unfortunately still work on PHP 4.
So TinyButStrong continue to be developed for both PHP 4 and PHP 5, and this is not a big effort.
The development class has code that work only for PHP 5 plus some specific fixes for PHP 4.
This development class can then be converted into a pure PHP 4 file or a pure PHP 5 file.
The tool 'tbs_build.php' does make this conversion.

Skrol29, 2010-05-27
*/

if (!isset($_GET['source'])) exit("Enter the name of a TBS development file in argument 'source' in the URL. Usually: <a href=\"".$_SERVER['SCRIPT_NAME']."?source=tbs_class_dev.php\">?source=tbs_class_dev.php</a>");

$Src = basename($_GET['source']);
if (!file_exists($Src)) exit("File '".$Src."' is not found.");

$SrcTxt = file_get_contents($Src);

// Check TBS version consistency
f_CheckVersionConsistency($SrcTxt, 'TBS', array('prefix'=>'Version  : ','suffix'=>' for PHP ','info'=>'in the header'), array('prefix'=>'$Version = \'','suffix'=>'\'','info'=>'in the property' ) );
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
echo "<u>Conversion into PHP 4:</u><br> \r\n";
$Txt = $SrcTxt;
$Ok = true;
$Ok = $Ok && f_Replace($Txt, 'public function ', 'function ', 'Change "public function"');
$Ok = $Ok && f_Replace($Txt, 'static function ', 'function ', 'Change "static function"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof Iterator)', '(is_a($SrcId,\'Iterator\'))', 'Change "instanceof Iterator"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof ArrayObject)', '(is_a($SrcId,\'ArrayObject\'))', 'Change "instanceof ArrayObject"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof IteratorAggregate)', '(is_a($SrcId,\'IteratorAggregate\'))', 'Change "instanceof IteratorAggregate"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof MySQLi)', '(is_a($SrcId,\'MySQLi\'))', 'Change "instanceof MySQLi"');
$Ok = $Ok && f_Replace($Txt, '($SrcId instanceof PDO)', '(is_a($SrcId,\'PDO\'))', 'Change "instanceof PDO"');
$Ok = $Ok && f_Replace($Txt, 'PDO::FETCH_ASSOC', 'PDO_FETCH_ASSOC', 'Change constant "PDO::FETCH_ASSOC"');
$Ok = $Ok && f_Replace($Txt, 'Zend_Db::FETCH_ASSOC', 'Zend_Db_FETCH_ASSOC', 'Change constant "Zend_Db::FETCH_ASSOC"');
$Ok = $Ok && f_Replace($Txt, "\ttry {"   , "\t if (true) { // try {", 'Avoid Try/Catch (1 on 2)');
$Ok = $Ok && f_Replace($Txt, "\t} catch ", "\t //} catch "          , 'Avoid Try/Catch (2 on 2)');
f_Update($Ok, $Dst4, $Txt);

// Conversion in PHP 5
echo "<br> \r\n";
echo "<u>Conversion into PHP 5:</u><br> \r\n";
$Txt = $SrcTxt;
$Ok = true;
$Ok = $Ok && f_Replace($Txt, 'if (PHP_VERSION===\'4.4.1\') {$this->RecSet = $this->SrcId;} else {$this->RecSet = &$this->SrcId;} // bad bug in PHP 4.4.1', '$this->RecSet = &$this->SrcId;', 'Delete bug 1 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, 'if (PHP_VERSION===\'4.4.1\') {$this->RecSet = $Query;} else {$this->RecSet = &$Query;}', '$this->RecSet = &$Query;', 'Delete bug 2 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, 'if ((PHP_VERSION===\'4.4.1\') and is_array($this->TBS->VarRef[$Item0])) {$Var = $this->TBS->VarRef[$Item0];} else {$Var = &$this->TBS->VarRef[$Item0];}', '$Var = &$this->TBS->VarRef[$Item0];', 'Delete bug 3 specific to PHP 4.4.1');
$Ok = $Ok && f_Replace($Txt, '$this->CurrRec = @pg_fetch_array($this->RecSet,$this->RecNum,PGSQL_ASSOC); // warning comes when no record left.', '$this->CurrRec = pg_fetch_assoc($this->RecSet);', 'Delete a fix for PostgreSQL on PHP<4.1.0');
$Ok = $Ok && f_Replace($Txt, "\n".'	var $', "\n".'	public $', 'Replace var declarations with public (1)');
$Ok = $Ok && f_Replace($Txt, "\n".'var $', "\n".'public $', 'Replace var declarations with public (2)');
$Ok = $Ok && f_Replace($Txt, ' = &new ', ' = new ', ' &new replaced with new');
$Ok = $Ok && f_Replace($Txt, ' for PHP 4', ' for PHP 5', 'version: for PHP 5');
$Ok = $Ok && f_Replace($Txt, 'if (version_compare(PHP_VERSION,\'4.0.6\')<0) echo \'<br><b>TinyButStrong Error</b> (PHP Version Check) : Your PHP version is \'.PHP_VERSION.\' while TinyButStrong needs PHP version 4.0.6 or higher.\';'."\n".'if (!is_callable(\'array_key_exists\')) {'."\n".'	function array_key_exists (&$key,&$array) {return key_exists($key,$array);}'."\n".'}'."\n".'if (!is_callable(\'property_exists\')) {'."\n".'	function property_exists(&$obj,$prop) {return true;}'."\n".'}', 'if (version_compare(PHP_VERSION,\'5.0\')<0) echo \'<br><b>TinyButStrong Error</b> (PHP Version Check) : Your PHP version is \'.PHP_VERSION.\' while TinyButStrong needs PHP version 5.0 or higher. You should try with TinyButStrong Edition for PHP 4.\';', 'check PHP version');
$Ok = $Ok && f_Replace($Txt, 'function clsTinyButStrong','function __construct','Rename constructor');
$Ok = $Ok && f_Replace($Txt, '			if (strcasecmp($x,substr($Txt,$p,$xl))!=0) continue; // For PHP 4 only'."\n", '', 'Avoid special check for strrpos()');
$Ok = $Ok && f_Replace($Txt, 'clsTinyButStrong::', 'self::', 'Replace clsTinyButStrong:: with self::');
f_Update($Ok, $Dst5, $Txt);

exit;

function f_Replace(&$Txt, $ReplWhat, $ReplWith, $Msg) {
// Replace strings in the source code	
	$NbrBefore = substr_count($Txt, $ReplWhat);
	$Txt = str_replace($ReplWhat, $ReplWith, $Txt);
	$NbrAfter = substr_count($Txt, $ReplWhat);
	if ($NbrBefore==0) {
		$ResMsg = f_Color('no items found','purple',false,true);
		$Ok = false;
	} elseif ($NbrAfter>0) {
		$ResMsg = f_Color('ERROR','red').'. '.$NbrBefore.' founds before, '.$NbrAfter.' found after.';
		$Ok = false;
	} else {
		$ResMsg = f_Color('OK','green').'. '.$NbrBefore.' founds before, '.$NbrAfter.' found after.';
		$Ok = true;
	}
	
	echo '- '.$Msg.': '.$ResMsg."<br> \r\n";
	
	return $Ok;
	
}

function f_Update($Ok, $Dst, $Txt) {
// Save the contents if $Ok and if it's different
	if ($Ok) {
		$Equal = false;
		if (file_exists($Dst)) {
			$TxtCurr = file_get_contents($Dst);
			if ($TxtCurr===$Txt) {
				echo "Conversion complete, already strictly equal to contents of file ".f_Color($Dst,'green')."<br> \r\n";
				return;
			}
		}
		$f = fopen($Dst, 'w');
		fwrite($f, $Txt);
		fclose($f);
		echo f_Color("Conversion complete and saved into file <b>".$Dst."</b>",'green',false)."<br> \r\n";
	} else {
		echo "Conversion with an unexepected result. No file change applied in any file.<br> \r\n";
	}
}


function f_Color($Txt, $Color,$Bold=true,$Italic=false) {
// Return formated spanned text
	return '<span style="color: '.$Color.'; '.(($Bold)? ' font-weight: bold;' : '').(($Italic)? ' font-style: italic;' : '').'">'.$Txt.'</span>';
}

function f_CheckVersionConsistency($SrcTxt, $Name, $v1, $v2) {
// Check that versions mentioned in two placed in the source are striclty equal.
// $v1 and $v2 must be arrays with keys 'prefix', 'suffix', 'info'
	echo $Name." version consistency:<br> \r\n";
	$v1_txt = f_TextBetween($SrcTxt, $v1['prefix'], $v1['suffix']);
	if ($v1_txt===false) exit("Check $Name version consistency: $Name version not found ".$v1['info']);
	$v2_txt= f_TextBetween($SrcTxt, $v2['prefix'], $v2['suffix']);
	if ($v2_txt===false) exit("Check $Name version consistency: $Name version not found ".$v2['info']);
	if ($v1_txt!==$v2_txt) exit("Check $Name version consistency: '<b>".$v1_txt."</b>' is mentioned ".$v1['info']." while '<b>".$v2_txt."</b>' is mentioned ".$v2['info'].".");
	echo '<span style="color: green; font-weight: bold;">OK</span> version '.$v1_txt."<br> \r\n";
}

function f_TextBetween($Txt, $str1, $str2) {
	$p1 = strpos($Txt, $str1);
	if ($p1===false) return false;
	$p1 = $p1+strlen($str1);
	$p2 = strpos($Txt, $str2, $p1);
	if ($p2===false) return false;
	return substr($Txt, $p1, $p2-$p1 );
}