<?php

// TbsSql Engine
// Version 2.7beta, 2010-06-10, Skrol29
// [ok] bug: Trace doesn't work when using TinyButStrong
// [  ] enh: Compatibility PHP 4
// [  ] fct: Trace info for connexion with user or not
// [  ] fct: Cache
// [  ] fct: Version

define('TBSSQL_SILENT', 0);
define('TBSSQL_NORMAL', 1);
define('TBSSQL_DEBUG', 2);
define('TBSSQL_TRACE', 3);
define('TBSSQL_DEFAULT', -1);

class clsTbsSql {

	function __construct($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		// Default values (defined here to be compatible with both PHP 4 & 5)
		$this->Version = '2.7beta';
		$this->Id = false;
		$this->SqlNull = 'NULL'; // can be modified by user
		$this->CacheEnabled = false;
		$this->CacheDir = '.';
		$this->CacheTimeout = 24*60; // in minutes
		$this->CacheSpecialTimeout = false;
		$this->CacheAutoClear = 100;
		$this->_CacheNewTimeout = false; // is different from false if data as to be saved
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
			if (!isset($GLOBALS[$var])) return $this->_Message('Automatic Connection: global variable \''.$var.'\' is not found.');
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
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$x = false;
		$Row = $this->_Dbs_RsFetch($RsId);
		if ($Row!==false) $x = reset($Row);
		$this->_Dbs_RsClose($RsId);
		return $x;
	}

	function GetRow($Sql) {
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$x = $this->_Dbs_RsFetch($RsId);
		$this->_Dbs_RsClose($RsId);
		return $x;
	}

	function GetRows($Sql) {
		
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		
		$Data = array();
		if ($this->_CacheTryRetrieve($Sql,$Data)) return $Data;
		
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		while ($r = $this->_Dbs_RsFetch($RsId)) {
			$Data[] = $r;
		}
		$this->_Dbs_RsClose($RsId);
		
		$this->_CacheTryUpdate($Data);
		return $Data;
		
	}

	function GetList($Sql) {
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen($Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$x = array();
		$first = true;
		while ($r = $this->_Dbs_RsFetch($RsId)) {
			if ($first) {
				$cols = array_keys($r);
				$col1 = $cols[0];
				$col2 = isset($cols[1]) ? $cols[1] : false; 
				unset($cols);
				$first = false;
			}
			if ($col2===false) {
				$x[] = $r[$col1];
			} else {
				$x[$r[$col1]] = $r[$col2];
			}
		}
		$this->_Dbs_RsClose($RsId);
		return $x;
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
			if ($this->_CacheNewTimeout===false) {
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
			if ($this->_CacheNewTimeout!==false) $this->_CacheTryUpdate($this->_CacheTbsData);
			return $this->_Dbs_RsClose($RsId);
		}
	}

  // Other private methods

	function _SqlError($ObjId) {
		if ($this->Mode==TBSSQL_SILENT) return;
		$x =  'Database error message: '.$this->_Dbs_Error($ObjId);
		if ($this->Mode==TBSSQL_DEBUG) $x .= "\r\nSQL = ".$this->LastSql;
		$this->_Message($x);
		return false;
	}

	function _Message($Txt,$Color='#FF0000') {
		if ($this->Mode!=TBSSQL_SILENT) {
			echo '<div style="color: '.$Color.';">[TbsSql] '.nl2br(htmlentities($Txt)).'</div>'."\r\n";
			flush();
		}
		return false;
	}

	function _TakeVar($VarName,$Key,&$Target,$MustBe=true) {
		if (isset($GLOBALS[$VarName][$Key])) {
			$Target = $GLOBALS[$VarName][$Key];
		} elseif ($MustBe) {
			$this->_Message('Automatic Connection: item \''.$Key.'\' is not found in the global variable \''.$VarName.'\'.');
			return false;
		}
		return true;
	}

	function _SqlDate($Date,$Mode) {
		// Return the date formated for the current Database
		if (is_string($Date)) {
			$x = strtotime($Date);
			if (($x===-1) or ($x===false)) {
				// display error message
				$this->_Message('Date value not recognized: '.$Date);
				$Mode = 0; // Try with the string mode
				$x = $Date;
			}
		} else {
			$x = $Date;
		}
		return $this->_Dbs_Date($x,$Mode);
	}

	function _SqlProtect($ArgLst,$Normal=true) {
	// Replace items (%i% , @i@ , #i#, ~i~) with the corresponding protected values
		$Sql = $ArgLst[0];
		$IdxMax = count($ArgLst) - 1;
		$ChrLst = array('%','@','#','~');
		for ($i=1;$i<=$IdxMax;$i++) {
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
			if ($this->Mode==TBSSQL_DEBUG) {
				$this->LastSql = $Sql;
			} elseif ($this->Mode==TBSSQL_TRACE) {
				$this->_Message('Trace SQL: '.$Sql,'#663399');
			}
		}
		return $Sql;
	}

  // cache functions

	function _CacheTryRetrieve($Sql, &$Data) {
	// Try to retrieve data from the cache file. Return true if $Data contains the data from the cache.
	
		// at this point $this->_CacheNewTimeout is always false
		$this->_CacheNewTimeout = false; // for security
	
		// update $this->_CacheTimeout and continue if has to retrieve data
		if ($this->CacheSpecialTimeout===false) {
			if ($this->CacheEnabled) {
				$timeout = $this->CacheTimeout;
			} else {
 				return false;
 			}
		} else {
			$timeout = $this->CacheSpecialTimeout;
			if ($timeout===TBSSQL_DEFAULT) $timeout = $this->CacheTimeout;
		}

		// 
		$this->_CacheFile = $this->CacheDir.'/cache_tbssql_'.md5($Sql).'.php'; // we save it as a PHP file in order to hide the contents from web users
		$now = time();
		//echo 'debug CacheTryRetrieve : cache file='.date('Y-m-d h:i:s',@filemtime($this->_CacheFile)).' , now='.date('Y-m-d h:i:s',$now)."<br>\r\n";
		if ( file_exists($this->_CacheFile) && ($now<=filemtime($this->_CacheFile)) ) {
			// retrieve the data
			// echo 'debug CacheTryRetrieve : cache still current'."<br>\r\n";
			include($this->_CacheFile); // set $CacheSql and $Data
			if ($Sql===$CacheSql) {
				if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data retreived from cache file '.$this->_CacheFile,'#663399');
				return true;
			} else {
				// It can happens very rarely that two different SQL queries have the same md5, with this chech we are sure to have to good result
				if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data not retreived from cache file '.$this->_CacheFile.' because SQL is different.','#663399');
				return false;
			}
		}
		
		//echo 'debug CacheTryRetrieve : cache to be updated.'."<br>\r\n";
		$this->_CacheNewTimeout = $timeout;
		$this->_CacheSql = $Sql;
		return false;
		
	}

	function _CacheTryUpdate($Data) {
	// Try to update the cache if necessary
		if ($this->_CacheNewTimeout===false) return false;
		$cache_end = time() + 60*$this->_CacheNewTimeout;
		$this->_CacheNewTimeout = false;
		$fid = @fopen($this->_CacheFile, 'w');
		if ($fid===false) {
			$this->_Message('The cache file '.$this->_CacheFile.' cannot be saved.');
			$ok = false;
		} else {
			flock($fid,2); // acquire an exlusive lock
			fwrite($fid,'<?php $CacheSql='.var_export($this->_CacheSql,true).'; ');
			fwrite($fid,'$Data='.var_export($Data,true).';');
			flock($fid,3); // release the lock
			fclose($fid);
			$ok = touch($this->_CacheFile, $cache_end);
		  //echo 'debug CacheTryUpdate : cache file='.date('Y-m-d h:i:s',filemtime($this->_CacheFile)).' end='.date('Y-m-d h:i:s',$cache_end).' , now='.date('Y-m-d h:i:s',time()).' , ok='.var_export($ok,true)."<br>\r\n";
			if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data saved in cache file '.$this->_CacheFile,'#663399');
			$ok = true;
		}
		unset($this->_CacheSql); // free a little bit of memory
		return $ok;
	}

	function _CacheCount() {

		$file = $this->CacheDir.'/cache_tbssql_count.php';

		if (file_exists($file)) include($file);
		if (!isset($count)) $count = 0;

		if ($count>=$this->CacheAutoClear) {
			$this->_CacheClearDir();
			$count = 1;
		} else {
			$count++;
		}

		// Updates the file
		$fid = @fopen($file, 'w');
		if ($fid===false) {
			$this->_Message('The counter file '.$file.' cannot be saved.');
		} else {
			flock($fid,2); // acquire an exlusive lock
			fwrite($fid,'<php? $count='.$count.';');
			flock($fid,3); // release the lock
			fclose($fid);
		}
	}
	
	function _CacheClearDir() {
		$del = array();
		$dir = opendir($this->CacheDir);
		$now = time();
		while ($file = readdir($dir)) {
			if ( (strlen($file)==47) && (substr($file,0,13)==='cache_tbssql_') ) {
				$fullpath = $this->CacheDir.'/'.$file;
				if (filectime($fullpath)>=$now) unlink($fullpath);
			}
		}
		closedir($dir);		
	}

// -------------------------------
// Specific to the Database System
// -------------------------------

// Database Engine: MySQL 
// Version 1.03, 2009-09-15, Skrol29
	
	function _Dbs_Prepare() {
		// If no connexion parameters are given, try to link to the current MySQL connection
		if (!is_resource($this->Id)) {
			if (@mysql_ping()) $this->Id = true;
		}
	}

	function _Dbs_Connect($srv,$uid,$pwd,$db,$drv) {
	// Should set $this->Id, value false means connection failed.

		// Information, must be the same for any database type	
		if ($this->Mode==TBSSQL_DEBUG) $this->LastSql = 'Connection String='.$srv;
		if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace Connection String: '.$srv,'#663399');

		$this->Id = @mysql_connect($srv,$uid,$pwd,true); // Force a new connection in order to manage connections to two different DB in the same script. Doesn't work if SQL Safe Mode is activated.
		
		// Chnage the current database
		if (($this->Id!==false) and ($db!=='')) {
			if (!@mysql_select_db($db, $this->Id)) $this->_SqlError(false);
		}

	}

	function _Dbs_Close() {
		if (is_resource($this->Id)) {
			return @mysql_close($this->Id);
		} else {
			return @mysql_close();
		}
	}

	function _Dbs_Error($ObjId) {
		if (is_resource($this->Id)) {
			return @mysql_error($ObjId);
		} else {
			return @mysql_error();
		}
	}
	
	function _Dbs_RsOpen($Sql) {
		if (is_resource($this->Id)) {
			return @mysql_query($Sql,$this->Id);
		} else {
			return @mysql_query($Sql);
		}
	}

	function _Dbs_RsFetch(&$RsId) {
		return mysql_fetch_assoc($RsId);
	}

	function _Dbs_RsClose(&$RsId) {
		if ($RsId===true)  return true;
		return @mysql_free_result($RsId);
	}
	
	function _Dbs_ProtectStr($Txt) {
		return mysql_escape_string($Txt);
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
		return $this->GetVal('SELECT LAST_INSERT_ID()');
	}
	
	function _Dbs_AffectedRows() {
		if (is_resource($this->Id)) {
			return mysql_affected_rows($this->Id);
		} else {
			return mysql_affected_rows();
		}
	}

}



?>
