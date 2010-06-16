<?php

// TbsSql Engine
// Version 2.7beta, 2010-06-10, Skrol29
/*
[ok] bug: Trace doesn't work when using TinyButStrong
[ok] fct: Cache
[ok] enh: Compatibility PHP 4
[ok] fct: Version (what for ?)
[ok] suffix in cache fil names in order to separate databases if needed
[  ] fct: Trace info for connexion with user or not
[  ] fct: TBSSQL_NOCACHE
[  ] fct: retour objet
*/

define('TBSSQL_SILENT', 0);
define('TBSSQL_NORMAL', 1);
define('TBSSQL_DEBUG', 2);
define('TBSSQL_TRACE', 3);
define('TBSSQL_1HOUR', 60);
define('TBSSQL_1DAY', 24*60);
define('TBSSQL_1WEEK', 7*24*60);

class clsTbsSql {

	function __construct($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		// Default values (defined here to be compatible with both PHP 4 & 5)
		$this->Version = '2.7beta';
		$this->Id = false;
		$this->SqlNull = 'NULL'; // can be modified by user
		$this->CacheDir = '.';
		$this->CacheTimeout = false; // in minutes
		$this->CacheSpecialTimeout = false;
		$this->CacheAutoClear = 7*24*60; // 1 week in minutes
		$this->CacheSuffix = '';
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
		if ($this->_CacheTryRetrieve($Sql,$Data)) return $this->_GetFirstRow($Data, true);
			
		if ($this->_CacheSql===false) {
			// no cache
			if (!$this->_GetData($Sql, $Data, true)) return false;
		} else {
			// cache has to be updated first
			if (!$this->_GetData($Sql, $Data)) return false;
			$this->_CacheUpdate($Data);
		}

		return $this->_GetFirstRow($Data, true);

	}

	function GetRow($Sql) {
		
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		
		$Data = array();
		if ($this->_CacheTryRetrieve($Sql,$Data)) return $this->_GetFirstRow($Data, false);
			
		if ($this->_CacheSql===false) {
			// no cache
			if (!$this->_GetData($Sql, $Data, true)) return false;
		} else {
			// cache has to be updated first
			if (!$this->_GetData($Sql, $Data)) return false;
			$this->_CacheUpdate($Data);
		}

		return $this->_GetFirstRow($Data, false);
		
	}

	function GetRows($Sql) {
		
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		
		$Data = array();
		if ($this->_CacheTryRetrieve($Sql,$Data)) return $Data;
		
		if (!$this->_GetData($Sql, $Data)) return false;
		
		if ($this->_CacheSql!==false) $this->_CacheUpdate($Data);
		
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
				if (!$this->_GetData($Sql, $Data)) return false;
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

	function _GetData($Sql, &$Data, $OnlyFirstRow=false) {
		
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
		return true;
	}

	function _GetFirstRow($Data, $FirstVal) {
		if (isset($Data[0])) {
			if ($FirstVal) {
				return reset($Data[0]);
			} else {
				return $Data[0];
			}
		} else {
			return false;
		}
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

  // cache functions

	function _CacheTryRetrieve($Sql, &$Data) {
	// Try to retrieve data from the cache file. Return true if $Data contains the data from the cache.
	
		// at this point $this->_CacheSql is always false
		$this->_CacheSql = false; // for security
	
		// check if cache is enabled
		if ($this->CacheSpecialTimeout===false) {
			if ($this->CacheTimeout===false) return false;
			$timeout = $this->CacheTimeout;
		} else {
			$timeout = $this->CacheSpecialTimeout;
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
				if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data retreived from cache file '.$this->_CacheFile,'#663399');
				return true;
			} else {
				// It can happens very rarely that two different SQL queries have the same md5, with this chech we are sure to have to good result
				if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data not retreived from cache file '.$this->_CacheFile.' because SQL is different.','#663399');
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
			$this->_Message('The cache file '.$this->_CacheFile.' cannot be saved.');
			$ok = false;
		} else {
			flock($fid,2); // acquire an exlusive lock
			fwrite($fid,'<?php $CacheSql='.var_export($this->_CacheSql,true).'; ');
			fwrite($fid,'$Data='.var_export($Data,true).';');
			flock($fid,3); // release the lock
			fclose($fid);
		  //echo 'debug CacheTryUpdate : cache file='.date('Y-m-d h:i:s',filemtime($this->_CacheFile)).' end='.date('Y-m-d h:i:s',$cache_end).' , now='.date('Y-m-d h:i:s',time()).' , ok='.var_export($ok,true)."<br>\r\n";
			if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: Data saved in cache file '.$this->_CacheFile,'#663399');
			$ok = true;
		}
		$this->_CacheSql = false;
		if ($this->CacheAutoClear!==false) $this->_CacheTryClearDir();
		return $ok;
	}

	function _CacheTryClearDir() {
	// Try to delete too old cache files
	
		$this->CacheAutoClear = false; // only one try per script call is enought, no need to check for each SQL query
		
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
		$file_len = $pref_len+30+$suff_len;
		while ($file = readdir($dir)) {
			if ( (strlen($file)==$file_len) && (substr($file,0,$pref)===$pref) && (substr($file,0,-$suff_len)===$suff) ) {
				$fullpath = $this->CacheDir.'/'.$file;
				if (filemtime($fullpath)<=$limit) $lst[] = $fullpath;
			}
		}
		closedir($dir);		

		foreach ($lst as $fullpath) {
			unlink($fullpath);
		}

		touch($check_file);

	  if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace SQL: '.count($lst).' old cache files have been deleted from the cache directory '.$this->CacheDir,'#663399');
		
	}

// -------------------------------
// Specific to the Database System
// -------------------------------

// Database Engine: SQL-Server via ODBC
// Version 1.03, 2010-06-10, Skrol29
	
	function _Dbs_Prepare() {
	}

	function _Dbs_Connect($srv,$uid,$pwd,$db,$drv) {
	// Should set $this->Id, value false means connection failed.

		// Retrieve the list of parameters given in the connection string
		if ( (strpos($srv,';')===false) and (strpos($srv,'=')===false) ) {
			// A sigle name is given as server name
			$str = 'SERVER='.$srv;
		} else {
			// several parameters ar given as server name => it taken as a string connection
			$str = trim($srv);
		}

		$prm_lst = array();
		$z = explode(';',$str);
		foreach ($z as $x) {
			$x = trim($x);
			$p = strpos($x,'=');
			if ($p===false) {
				$prm_lst[strtoupper($x)] = '';
			} else {
				$prm_lst[strtoupper(substr($x,0,$p))] = substr($x,$p+1);
			}
		}
		unset($z);

		// Complete the connextion string.
		if (!isset($prm_lst['DRIVER'])) {
			// Parameter DRIVER must exist if parameter DSN is not defined
			if ( ($drv=='') and (!isset($prm_lst['DSN'])) ) {
				if (strtolower(substr(PHP_OS,0,3))=='win') {
					$drv = '{SQL Server}'; // Windows OS
				} else {
					$drv = 'FreeTDS'; // Linux OS: There are only two SQL Server drivers for UnixODBC: Easysoft (driver='Easysoft ODBC-SQL Server') and FreeTDS
				}
			}
			if ($drv!=='') $str .= ';DRIVER='.$drv; // Optional if a DSN is defined.
		} 
		if ( (!isset($prm_lst['DATABASE'])) and ($db!=='') ) $str .= ';DATABASE='.$db; // Parameter DATABASE is a priority over the DSN parameters.
		if ((count($prm_lst)==1) and (substr($str,-1,1)!=';') ) $str .= ';'; // If it is a connextion string then at least on semilicon must be present (tested with for 'Easysoft ODBC-SQL Server' driver)

		// Information, must be the same for any database type
		if ($this->Mode==TBSSQL_DEBUG) $this->LastSql = 'Connection String='.$str;
		if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace Connection String: '.$str,'#663399');
		
		$this->Id = odbc_connect($str,$uid,$pwd); // do not use @ before the function because the non installation of the extension on Linux makes a fatal error.

	}

	function _Dbs_Close() {
		odbc_close($this->Id);
	}

	function _Dbs_Error($ObjId) {
		if ($ObjId===false) {
			return @odbc_errormsg();
		} else {
			return @odbc_errormsg($ObjId);
		}
	}

	function _Dbs_RsOpen($Sql) {
		$RsId = @odbc_exec($this->Id,$Sql);
		if ($RsId!==false) {
			$this->ColumnLst = array();
			$iMax = odbc_num_fields($RsId);
			for ($i=1;$i<=$iMax;$i++) {
				$this->ColumnLst[$i] = ''.odbc_field_name($RsId,$i);
			}
		}
		return $RsId;
	}

	function _Dbs_RsFetch(&$RsId) {
		if (odbc_fetch_row($RsId)) {
			$Rec = array();
			foreach ($this->ColumnLst as $col_id=>$col_name) {
				$Rec[$col_name] = odbc_result($RsId,$col_id);
			}
		} else {
			$Rec = false;
		}
		return $Rec;
	}

	function _Dbs_RsClose(&$RsId) {
		return @odbc_free_result($RsId);
	}
	
	function _Dbs_ProtectStr($Txt) {
		return str_replace('\'','\'\'',$Txt);
	}
	
	function _Dbs_Date($Timestamp,$Mode) {
		switch ($Mode) {
		case 1:
			// Date only
			return '{d \''.date('Y-m-d',$Timestamp).'\'}'; // Works both for proc-stock and functions
		case 2:
			// Date and time
			$ms = date('u',$Timestamp);
			if ($ms==='u') {
				$ms = '000'; // 'u' is supported since PHP 5.2.2 only
			} else {
				$ms = substr($ms,0,3); // only 3 digits for the milliseconds for ODBC
			}
			return '{ts \''.date('Y-m-d H:i:s',$Timestamp).'.'.$ms.'\'}';
		case 0:
			// Value is a string
			return '\''.$this->_Dbs_ProtectStr($Timestamp).'\'';
		default:
			// Error in date recognization
			return '\'0000-00-00\'';
		}  
	}

	function _Dbs_LastRowId() {
		return $this->GetVal('SELECT @@IDENTITY');
	}
	
	function _Dbs_AffectedRows() {
		return $this->GetVal('SELECT @@ROWCOUNT');
	}

}



?>
