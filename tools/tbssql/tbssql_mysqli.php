<?php

// TbsSql Engine
// Version 3.0beta, 2010-07-07, Skrol29
/*
[ok] bug: Trace doesn't work when using TinyButStrong
[ok] fct: Cache
[ok] enh: Compatibility PHP 4
[ok] fct: Version (what for ?)
[ok] suffix in cache file names in order to separate databases if needed
[ok] fct: TBSSQL_NOCACHE
[ok] fct: return records as objects
[ok] fct: debug grid
[ok] fct: console
[  ] fct: Trace info for connexion with user or not
*/

if ( (version_compare(PHP_VERSION,'5')<0) && (!function_exists('clone'))  ) {
	eval('function clone($object) {return $object;}'); // eval is needed because the syntax function clone() is refused in PHP 5
}

define('TBSSQL_SILENT', 0);
define('TBSSQL_NORMAL', 1);
define('TBSSQL_DEBUG', 2);
define('TBSSQL_TRACE', 4);
define('TBSSQL_GRID', 8);
define('TBSSQL_CONSOLE', 16);
define('TBSSQL_1HOUR', 60);
define('TBSSQL_1DAY', 24*60);
define('TBSSQL_1WEEK', 7*24*60);
define('TBSSQL_NOCACHE', -1);
define('TBSSQL_ARRAY', 'array');
define('TBSSQL_OBJECT', 'object');

class clsTbsSql {

	function __construct($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		// Default values (defined here to be compatible with both PHP 4 & 5)
		$this->Version = '3.0-beta-2010-07-07';
		$this->Id = false;
		$this->SqlNull = 'NULL'; // can be modified by user
		$this->DefaultRowType = TBSSQL_ARRAY;
		$this->CacheDir = '.';
		$this->CacheTimeout = false; // in minutes
		$this->CacheSpecialTimeout = false;
		$this->CacheAutoClear = TBSSQL_1WEEK; // 1 week in minutes
		$this->CacheSuffix = '';
		$this->InitMsg = true;
		$this->InitCsl = true;
		$this->_CacheSql = false; // is different from false if data as to be saved
		if ($srv==='') {
			$this->Mode = $Mode;
		} else {
			$this->Connect($srv,$uid,$pwd,$db,$drv,$Mode); // Try to connect when instance is created
		}
		$this->_Dbs_Prepare();
		$this->SetTbsKey();
	}

	// Compatibility for PHP 4
	function clsTbsSql($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		$this->__construct($srv,$uid,$pwd,$db,$drv,$Mode);
	}

// Public methods
	
	function Connect($srv,$uid='',$pwd='',$db='',$drv='',$Mode=false) {
		if ($Mode!==false) $this->Mode = $Mode;
		$auto = (($srv!='') and ($uid.$pwd.$db.$drv==''));
		if ($auto) {
			// Connection by a global variable
			$var = $srv;
			if (!isset($GLOBALS[$var])) return $this->_Message('[Error] Automatic Connection failed because the global variable \''.$var.'\' is not found.');
			if (!$this->_TakeVar($var,'srv',$srv)) return false;
			if (!$this->_TakeVar($var,'uid',$uid)) return false;
			if (!$this->_TakeVar($var,'pwd',$pwd)) return false;
			if (!$this->_TakeVar($var,'db' ,$db)) return false;
			$this->_TakeVar($var,'drv',$drv,false);
			unset($GLOBALS[$var]);
		}
		$this->_Dbs_Connect($srv,$uid,$pwd,$db,$drv);
		if (is_null($this->Id)) $this->Id = false;
		if ($this->Id===false) return $this->_SqlError(false);
		return true;
	}

	function Close() {
		if ($this->Id!==false) $this->_Dbs_Close();
	}

	function Execute($Sql) {
		// we do not use cache here
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$this->_Dbs_RsClose($RsId);
		return true;
	}

	function GetVal($Sql) {

		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		
		$Data = array();
		if (!$this->_CacheTryRetrieve($Sql,$Data)) {
			if (!$this->_GetDataFromDb($Sql, $Data, ($this->_CacheSql===false))) return false; // if CacheSql is false, then we do not need to retrieve all the records from the db
			if ($this->_CacheSql!==false) $this->_CacheUpdate($Data);
		}

		if (count($Data)==0) {
			return false;
		} else {
			return reset($Data[0]);
		}

	}

	function GetRow($Arg0) {
		
		$ArgLst = func_get_args();

		$ObjConv = $this->_ObjCheck($Arg0, $ArgLst);
		$SqlPos = ($ObjConv) ? 1 : 0;
		$Sql = $this->_SqlProtect($ArgLst, $SqlPos);
		
		$Data = array();
		if (!$this->_CacheTryRetrieve($Sql,$Data)) {
			if (!$this->_GetDataFromDb($Sql, $Data, ($this->_CacheSql===false))) return false; // if CacheSql is false, then we do not need to retrieve all the records from the db
			if ($this->_CacheSql!==false) $this->_CacheUpdate($Data);
		}

		if (count($Data)==0) return false;
		
		$Data = array($Data[0]);

		// conversion to object		
		if ($ObjConv) {
			$this->_ObjConversion($Data, $Arg0);
		} elseif ($this->DefaultRowType!==TBSSQL_ARRAY) {
			$this->_ObjConversion($Data, $this->DefaultRowType);
		}

		return $Data[0];

	}

	function GetRows($Arg0) {

		$ArgLst = func_get_args();

		$ObjConv = $this->_ObjCheck($Arg0, $ArgLst);
		$SqlPos = ($ObjConv) ? 1 : 0;
		$Sql = $this->_SqlProtect($ArgLst, $SqlPos);

		$Data = array();
		if (!$this->_CacheTryRetrieve($Sql,$Data)) {
			if (!$this->_GetDataFromDb($Sql, $Data)) return false;
			if ($this->_CacheSql!==false) $this->_CacheUpdate($Data);
		}

		// conversion to object		
		if ($ObjConv) {
			$this->_ObjConversion($Data, $Arg0);
		} elseif ($this->DefaultRowType!==TBSSQL_ARRAY) {
			$this->_ObjConversion($Data, $this->DefaultRowType);
		}
		
		return $Data;
		
	}

	function GetList($Sql) {
		
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		
		$Data = array();
		$col1 = false;
		$col2 = false;

		if (!$this->_CacheTryRetrieve($Sql,$Data)) {
			if ($this->_CacheSql===false) {
				// no cache
				$RsId = $this->_Dbs_RsOpen($Sql);
				if ($RsId===false) return $this->_SqlError($this->Id);
				$r = $this->_Dbs_RsFetch($RsId); // First row
				if ($r===false) return $Data;
				$this->_ItemFoundCol($r, $col1, $col2);
				$this->_ItemAdd($Data, $r, $col1, $col2);
				while ($r = $this->_Dbs_RsFetch($RsId)) $this->_ItemAdd($Data, $r, $col1, $col2); // other rows
				$this->_Dbs_RsClose($RsId);
				return $Data;
			} else {
				if (!$this->_GetDataFromDb($Sql, $Data)) return false;
				$this->_CacheUpdate($Data);
			}
		}
		
		// At this point, $Data contain bold data.
		$Data2 = array();
		if (!isset($Data[0])) return $Data2;
		$this->_ItemFoundCol($Data[0], $col1, $col2);
		foreach ($Data as $r) $this->_ItemAdd($Data2, $r, $col1, $col2);
		return $Data2;
		
	}

	function GetSql($Sql) {
		$ArgLst = func_get_args();
		return $this->_SqlProtect($ArgLst);
	}

	function AffectedRows() {
		return $this->_Dbs_AffectedRows();
	}

	function LastRowId() {
		return $this->_Dbs_LastRowId();
	}

	function SetTbsKey($Key='') {
		// Define the key (or a new key) for the TinyButStrong Template Engine
		global $_TBS_UserFctLst;
		if ($Key=='') {
			// Search for an free key
			$Key = 'tbssql'; // default key
			$i = 1;
			while (isset($_TBS_UserFctLst['k:'.$Key])) {
				$i++;
				$Key = 'tbssql'.$i;
			}
		}
		$this->TbsKey = $Key;
		$this->_Tbs_FetchFct = '_Tbs_RsFetch_Default'; // saved in a property in order to be passed by reference and then be modified when cache is enabled
		$_TBS_UserFctLst['k:'.$Key] = array('type'=>4,'open'=>array(&$this,'_Tbs_RsOpen'),'fetch'=>array(&$this,&$this->_Tbs_FetchFct),'close'=>array(&$this,'_Tbs_RsClose'));
	}

// Private methods

	// Special for TinyButStrong
	// -------------------------
	function _Tbs_RsOpen($Src,$Sql) {
		$Sql = $this->_SqlProtect(array($Sql)); // just in order to manage the Mode when it is TRACE or DEBUG
		$this->_CacheTbsData = array(); // default value in case of data to be saved
		if ($this->_CacheTbsOk=$this->_CacheTryRetrieve($Sql,$this->_CacheTbsData)) {
			// Data is retrieved from the cache
			$this->_CacheTbsEnd = count($this->_CacheTbsData);
			$this->_CacheTbsCurr = -1;
			$this->_Tbs_FetchFct = '_Tbs_RsFetch_FromCache'; // from cache
			$RecSet = true;
		} else {
			// No cache
			$RecSet = $this->_Dbs_RsOpen($Sql);
			if ($RecSet===false) $this->_SqlError(false);
			if ($this->_CacheSql===false) {
				$this->_Tbs_FetchFct = '_Tbs_RsFetch_Default'; // no cache
			} else {
				$this->_Tbs_FetchFct = '_Tbs_RsFetch_ToBeSaved'; // cache to be saved
			}
		}
		return $RecSet;
	}

	function _Tbs_RsFetch_Default(&$RsId) {
		// no cache
		return $this->_Dbs_RsFetch($RsId);
	}
	function _Tbs_RsFetch_FromCache(&$RsId) {
		// read data from cache
		$this->_CacheTbsCurr++;
		if ($this->_CacheTbsCurr>=$this->_CacheTbsEnd) {
			return false;
		} else {
			return $this->_CacheTbsData[$this->_CacheTbsCurr];
		}
	}	
	function _Tbs_RsFetch_ToBeSaved(&$RsId) {
		// data to be saved
		$Rec = $this->_Dbs_RsFetch($RsId);
		if ($Rec!==false) $this->_CacheTbsData[] = $Rec;
		return $Rec;
	}
	
	function _Tbs_RsClose(&$RsId) {
		if ($this->_CacheTbsOk) {
			unset($this->_CacheTbsData); // free memory
			return true;
		} else {
			if ($this->_CacheSql!==false) $this->_CacheUpdate($this->_CacheTbsData);
			return $this->_Dbs_RsClose($RsId);
		}
	}

  // Other private methods

	function _SqlError($ObjId) {
		if ($this->Mode===TBSSQL_SILENT) return;
		$x =  'Database error message: '.$this->_Dbs_Error($ObjId);
		if ($this->_ModeHas(TBSSQL_DEBUG)) $x .= "\r\nSQL = ".$this->LastSql;
		$this->_Message($x);
		return false;
	}

	function _Message($Txt,$Color='#FF0000') {
	// display a message right now in the Html page or in the console. Must return false.

		if ($this->Mode===TBSSQL_SILENT) return false;

		if ($this->InitMsg) {
			// display the TbsSql version the very first time and continue
			$this->InitMsg = false;
			$this->_Message('[Trace]: TbsSql version '.$this->Version, '#060');
		}

		if (is_array($Txt)) {
			$Html =  $this->_HtmlGrid($Txt);
		} else {
			$Html = '<div style="color: '.$Color.';">[TbsSql]'.nl2br(htmlentities($Txt)).'</div>'."\r\n";
		}

		if ($this->_ModeHas(TBSSQL_CONSOLE)) {
			$Html = addslashes($Html); // apply to ('), ("), (\) and (null)
			$Html = str_replace(array("\n","\r","\t"),array('\n','\r','\t'),$Html);
			if ($this->InitCsl) {
				$this->InitCsl = false;
				$m1 = 100;
				$Html = '
<script type="text/javascript"> /* code generated by TbsSql */
var TbsSqlConsole = window.open("","TbsSql_Console","top='.$m1.',left="+(screen.width-screen.height+'.$m1.')+",width="+(screen.height-'.(2*$m1).')+",height="+(screen.height-'.(2*$m1).')+",resizable=yes,scrollbars=yes,location=no,status=no,toolbar=no,menubar=no,directories=no");
TbsSqlConsole.document.write(\'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>TbsSQL Console</title></head><body>'.$Html.'</body></html>\');
</script>
';
			} else {
				$Html = '<script type="text/javascript">/* code generated by TbsSql */ TbsSqlConsole.document.body.innerHTML += "'.$Html.'";</script>';
			}
		}

		echo $Html;
		flush();
		return false;
		
	}

	function _ModeHas($Option) {
		return (($this->Mode & $Option)===$Option);
	}

	function _TakeVar($VarName,$Key,&$Target,$MustBe=true) {
	// Read gloabl variables for automatic connexion
		if (isset($GLOBALS[$VarName][$Key])) {
			$Target = $GLOBALS[$VarName][$Key];
		} elseif ($MustBe) {
			$this->_Message('[Error]: Automatic Connection failed because the item \''.$Key.'\' is not found in the global variable \''.$VarName.'\'.');
			return false;
		}
		return true;
	}

	function _ObjCheck($Arg0, $ArgLst) {
	// Check if an object conversion is asked
		if (is_string($Arg0)) {
			if( (strpos($Arg0,' ')===false) && (strpos($Arg0,"\r")===false) && (strpos($Arg0,"\n")===false) && (count($ArgLst)>1) ) return true;
		} elseif (is_object($Arg0)) {
			return true;
		}
		return false;
	}

	function _ObjConversion(&$Data, $ObjRef) {
	// Convert the records from arrays to objects
	// Note: foreach() is twice faster than array_walk() for both record and column loops.
		if (is_object($ObjRef)) {
			foreach ($Data as $idx => $rec) {
				$obj = clone($ObjRef);
				foreach ($rec as $col => $val) {$obj->$col = $val;}
				$Data[$idx] = $obj;
			}
		} else {
			if ($ObjRef==='array') return;
			if (($ObjRef==='') || ($ObjRef==='object')) $ObjRef = 'stdClass';
			foreach ($Data as $idx => $rec) {
				$obj = new $ObjRef; // the class name
				foreach ($rec as $col => $val) {$obj->$col = $val;}
				$Data[$idx] = $obj;
			}
		}
	}

	function _SqlDate($Date,$Mode) {
		// Return the date formated for the current Database
		if (is_string($Date)) {
			$x = strtotime($Date);
			if (($x===-1) or ($x===false)) {
				// display error message
				$this->_Message('[Error]: Date value not recognized: '.$Date);
				$Mode = 0; // Try with the string mode
				$x = $Date;
			}
		} else {
			$x = $Date;
		}
		return $this->_Dbs_Date($x,$Mode);
	}

	function _SqlProtect($ArgLst, $SqlPos=0, $Normal=true) {
	// Replace items (%i% , @i@ , #i#, ~i~) with the corresponding protected values
		$Sql = $ArgLst[$SqlPos];
		$IdxMax = count($ArgLst) - 1;
		$ChrLst = array('%','@','#','~');
		for ($i=$SqlPos+1;$i<=$IdxMax;$i++) {
			for ($t=0;$t<=1;$t++) {
				if ($t==0) {
					// Scann normal tags
					$ValIsNull = false;
					$iz = ''.$i;
				} else {
					// Scann nullable tags 
					$ValIsNull = ( is_null($ArgLst[$i]) or ($ArgLst[$i]===false) );					
					$iz = '['.$i.']';
				}
				foreach ($ChrLst As $Chr) {
					$tag = $Chr.$iz.$Chr;
					if (strpos($Sql,$tag)!==false) {
						if ($ValIsNull) {
							$x = $this->SqlNull;
						} elseif ($Chr==='%') {
							$x = $this->_Dbs_ProtectStr(''.$ArgLst[$i]); // Simple value
						} elseif ($Chr==='@') {
							$x = $x = '\''.$this->_Dbs_ProtectStr(''.$ArgLst[$i]).'\''; // String value
						} elseif ($Chr==='#') {
							$x = $x = $this->_SqlDate($ArgLst[$i],1); // Date value
						} elseif ($Chr==='~') {
							$x = $x = $this->_SqlDate($ArgLst[$i],2); // Date and time value
						}
						$Sql = str_replace($tag,$x,$Sql) ;
					}
				}
			}
		}
		if ($Normal) {
			if ($this->_ModeHas(TBSSQL_DEBUG)) {
				$this->LastSql = $Sql;
			} elseif ($this->_ModeHas(TBSSQL_TRACE)) {
				$this->_Message('[Trace]: '.$Sql,'#663399');
			}
		}
		return $Sql;
	}

	function _GetDataFromDb($Sql, &$Data, $OnlyFirstRow=false) {
		
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		
		$Data = array();
		if ($OnlyFirstRow) {
			$r =  $this->_Dbs_RsFetch($RsId);
			if ($r!==false) $Data[] = $r;
		} else {
			while ($r = $this->_Dbs_RsFetch($RsId)) {
				$Data[] = $r;
			}
		}
		
		$this->_Dbs_RsClose($RsId);
		
		if ($this->_ModeHas(TBSSQL_GRID)) $this->_Message($Data);
		
		return true;
	}

	function _ItemFoundCol($Row, &$col1, &$col2) {
		if ($Row===false) return false;
		$col_lst = array_keys($Row);
		$col1 = $col_lst[0];
		$col2 = isset($col_lst[1]) ? $col_lst[1] : false;
		return true;
	}
	
	function _ItemAdd(&$Data, $Row, $col1, $col2) {
		if ($col2===false) {
			$Data[] = $Row[$col1];	
		} else {
			$Data[$Row[$col1]] = $Row[$col2];	
		}
	}

// trace functions
// ---------------

	function _HtmlGrid($Data) {

		$nl = "\n";
		$nbsp = '&nbsp;';
		$firstrow = true;
		$row_bg = array(0=>'#faebd7', 1=>'#fff8dc');
		$row_idx = 0;

		$html = $nl.'<table border="1" cellspacing="0" cellpadding="2">';
		
		foreach ($Data as $id => $row) {
			if ($firstrow) {
				$html .= $nl.' <tr bgcolor="#999999"><td>#</td>';
				foreach ($row as $col => $val) $html .= '<td>'.str_replace(' ',$nbsp,$col).'</td>';
				$html .= '</tr>';
				$firstrow = false;
			}
			$html .= $nl.' <tr bgcolor="'.$row_bg[$row_idx].'"><td>'.$id.'</td>';
			foreach ($row as $col => $val) {
				$x = str_replace(' ',$nbsp,strval($val));
				if ($x==='') $x = $nbsp;
				$html .= '<td>'.$x.'</td>';
			}
			$html .= '</tr>';
			$row_idx++;
			if ($row_idx>1) $row_idx=0;
		}
		
		if ($firstrow) {
			$html .= $nl.' <tr bgcolor="'.$row_bg[0].'"><td>No data</td></tr>';
		}
		
		$html .= $nl.'</table>';

		return $html;

	}

	function _TraceGetTime() {
	// return the current timer in milli-seconds
		$x = microtime();
		$p = strpos($x,' ');
		if ($p===false) return (float)0;
		$x = substr($x,$p+1).substr($x,1,$p);
		return (float)$x;
	}

// cache functions
// ---------------

	function _CacheTryRetrieve($Sql, &$Data) {
	// Try to retrieve data from the cache file. Return true if $Data contains the data from the cache.
	
		// at this point $this->_CacheSql is always false
		$this->_CacheSql = false; // for security
	
		// check if cache is enabled
		if ($this->CacheSpecialTimeout===false) {
			if (($this->CacheTimeout===false) || ($this->CacheTimeout===TBSSQL_NOCACHE)) return false;
			$timeout = $this->CacheTimeout;
		} else {
			$timeout = $this->CacheSpecialTimeout;
			$this->CacheSpecialTimeout = false;
			if ($timeout===TBSSQL_NOCACHE) return false;
		}

		// 
		$this->_CacheFile = $this->CacheDir.'/cache_tbssql_'.md5($Sql).$this->CacheSuffix.'.php'; // we save it as a PHP file in order to hide the contents from web users
		$now = time();
		//echo 'debug CacheTryRetrieve : timout= '.$timeout.', cache file='.date('Y-m-d h:i:s',@filemtime($this->_CacheFile)).' , limit='.date('Y-m-d h:i:s',@filemtime($this->_CacheFile)+60*$timeout).' , now='.date('Y-m-d h:i:s',$now)."<br>\r\n";
		if ( file_exists($this->_CacheFile) && ($now<=(filemtime($this->_CacheFile)+60*$timeout)) ) {
			// retrieve the data
			// echo 'debug CacheTryRetrieve : cache still current'."<br>\r\n";
			include($this->_CacheFile); // set $CacheSql and $Data
			if ($Sql===$CacheSql) {
				if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Trace]: Data retrieved from cache file '.$this->_CacheFile,'#060');
				if ($this->_ModeHas(TBSSQL_GRID))  $this->_Message($Data);
				return true;
			} else {
				// It can happens very rarely that two different SQL queries have the same md5, with this chech we are sure to have to good result
				if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Trace]: Data not retrieved from cache file '.$this->_CacheFile.' because SQL is different.','#060');
				return false;
			}
		}
		
		//echo 'debug CacheTryRetrieve : cache to be updated.'."<br>\r\n";
		$this->_CacheSql = $Sql;
		return false;
		
	}

	function _CacheUpdate($Data) {
	// Update the cache

		$fid = @fopen($this->_CacheFile, 'w');
		if ($fid===false) {
			$this->_Message('[Error]: The cache file '.$this->_CacheFile.' cannot be saved.');
			$ok = false;
		} else {
			flock($fid,2); // acquire an exlusive lock
			fwrite($fid,'<?php $CacheSql='.var_export($this->_CacheSql,true).'; ');
			fwrite($fid,'$Data='.var_export($Data,true).';');
			flock($fid,3); // release the lock
			fclose($fid);
		  //echo 'debug CacheTryUpdate : cache file='.date('Y-m-d h:i:s',filemtime($this->_CacheFile)).' end='.date('Y-m-d h:i:s',$cache_end).' , now='.date('Y-m-d h:i:s',time()).' , ok='.var_export($ok,true)."<br>\r\n";
			if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Trace]: Data saved in cache file '.$this->_CacheFile,'#060');
			$ok = true;
		}
		$this->_CacheSql = false;
		if ($this->CacheAutoClear!==false) {
			$this->_CacheTryClearDir();
			$this->CacheAutoClear = false; // only one try per script call is enought, no need to check for each SQL query
		}
		return $ok;
	}

	function _CacheTryClearDir() {
	// Try to delete too old cache files
	
		$check_file = $this->CacheDir.'/cache_tbssql_info.php';
		
		// if the check file does not exist yet, we create it
		if (!file_exists($check_file)) {
			touch($check_file);
			return;
		}

		// if the check file is too young, we do not clear
		$limit = time()-60*$this->CacheAutoClear;
		if (filemtime($check_file)>$limit) return;
		
		// clear the directory
		$lst = array();
		$dir = opendir($this->CacheDir);
		$pref = 'cache_tbssql_';
		$suff = $this->CacheSuffix.'.php';
		$pref_len = strlen($pref);
		$suff_len = strlen($suff);
		$file_len = $pref_len+strlen(md5(''))+$suff_len;
		while ($file = readdir($dir)) {
			if ( (strlen($file)==$file_len) && (substr($file,0,$pref_len)===$pref) && (substr($file,-$suff_len)===$suff) ) {
				$fullpath = $this->CacheDir.'/'.$file;
				if (filemtime($fullpath)<=$limit) $lst[] = $fullpath;
			}
		}
		closedir($dir);		

		foreach ($lst as $fullpath) {
			unlink($fullpath);
		}

		touch($check_file);

	  if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Trace]: CacheAutoClear has deleted '.count($lst).' old cache files from directory '.$this->CacheDir,'#060');

	}

// -------------------------------
// Specific to the Database System
// -------------------------------

// Database Engine: MySQL, using the MySQL Improved Extension
// Version 1.03, 2010-06-10, Skrol29
	
	function _Dbs_Prepare() {
		$this->Version .= '/1.03 for MySQL with Improved Extrension (mysqli)';
		return true;
	}

	function _Dbs_Connect($srv,$uid,$pwd,$db,$drv) {
	// Should set $this->Id, value false means connection failed.

		// Information, must be the same for any database type	
		if ($this->Mode==TBSSQL_DEBUG) $this->LastSql = 'Connection String='.$srv;
		if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace Connection String: '.$srv,'#663399');

		$this->Id = @new MySQLi($srv,$uid,$pwd,$db);
		if (mysqli_connect_errno()!=0) $this->Id = false;

	}

	function _Dbs_Close() {
		$this->Id->close();
		$this->Id = false;
	}

	function _Dbs_Error($ObjId) {
		if ($this->Id===false) {
			return mysqli_connect_error();
		} else {
			return $this->Id->error;
		}
	}

	function _Dbs_RsOpen($Sql) {
		return $this->Id->query($Sql,MYSQLI_USE_RESULT);
	}

	function _Dbs_RsFetch(&$RsId) {
		$x = $RsId->fetch_assoc();
		if (is_null($x)) $x = false;
		return $x;
	}

	function _Dbs_RsClose(&$RsId) {
		if (is_object($RsId)) $RsId->free_result(); // Maty not be an object for Non Select statements
		return true;
	}
	
	function _Dbs_ProtectStr($Txt) {
		return $this->Id->real_escape_string($Txt);
	}
	
	function _Dbs_Date($Timestamp,$Mode) {
		switch ($Mode) {
		case 1:
			// Date only
			return '\''.date('Y-m-d',$Timestamp).'\'';
		case 2:
			// Date and time
			return '\''.date('Y-m-d H:i:s',$Timestamp).'\'';
		case 0:
			// Value is a string
			return '\''.$this->_Dbs_ProtectStr($Timestamp).'\'';
		default:
			// Error in date recognization
			return '\'0000-00-00\'';
		}  
	}

	function _Dbs_LastRowId() {
		return $this->Id->insert_id;
	}
	
	function _Dbs_AffectedRows() {
		return $this->Id->affected_rows;
	}

}