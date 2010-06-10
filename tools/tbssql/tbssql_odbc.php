<?php

// TbsSql Engine
// Version 2.6, 2009-11-26, Skrol29

define('TBSSQL_SILENT', 0);
define('TBSSQL_NORMAL', 1);
define('TBSSQL_DEBUG', 2);
define('TBSSQL_TRACE', 3);

class clsTbsSql {

	function clsTbsSql($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		// Default values (defined here to be compatible with both PHP 4 & 5)
		$this->Id = false;
		$this->SqlNull = 'NULL'; // can be modifier by user
		if ($srv==='') {
			$this->Mode = $Mode;
		} else {
			$this->Connect($srv,$uid,$pwd,$db,$drv,$Mode); // Try to connect when instance is created
		}
		$this->_Dbs_Prepare();
		$this->SetTbsKey();
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
		$RsId = $this->_Dbs_RsOpen(null,$Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$this->_Dbs_RsClose($RsId);
		return true;
	}

	function GetVal($Sql) {
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen(null,$Sql);
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
		$RsId = $this->_Dbs_RsOpen(null,$Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$x = $this->_Dbs_RsFetch($RsId);
		$this->_Dbs_RsClose($RsId);
		return $x;
	}

	function GetRows($Sql) {
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen(null,$Sql);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$x = array();
		while ($r = $this->_Dbs_RsFetch($RsId)) {
			$x[] = $r;
		}
		$this->_Dbs_RsClose($RsId);
		return $x;
	}

	function GetList($Sql) {
		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);
		$RsId = $this->_Dbs_RsOpen(null,$Sql);
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
		$_TBS_UserFctLst['k:'.$Key] = array('type'=>4,'open'=>array(&$this,'_Dbs_RsOpen'),'fetch'=>array(&$this,'_Dbs_RsFetch'),'close'=>array(&$this,'_Dbs_RsClose'));
	}

// Private methods

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

// Deprecated

	function Value($DefVal,$Sql) {
		$ArgLst = func_get_args();
		array_shift($ArgLst);
		$Sql = $this->_SqlProtect($ArgLst,false);
		$x = $this->GetVal($Sql);
		if ($x===false) $x = $DefVal;
		return $x;
	}

	function Row1($Sql) {
		$Sql = $this->_SqlProtect(func_get_args(),false);
		return $this->GetRow($Sql);
	}

	function Rows($Sql) {
		$Sql = $this->_SqlProtect(func_get_args(),false);
		return $this->GetRows($Sql);
	}

// -------------------------------
// Specific to the Database System
// -------------------------------

// Database Engine: ODBC (Linux or Windows)
// Version 1.00, 2009-11-24, Skrol29
	
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
/*		if (!isset($prm_lst['DRIVER'])) {
			// Parameter DRIVER must exist if parameter DSN is not defined
			if ( ($drv=='') and (!isset($prm_lst['DSN'])) ) {
				if (strtolower(substr(PHP_OS,0,3))=='win') {
					$drv = '{SQL Server}'; // Windows OS
				} else {
					$drv = 'FreeTDS'; // Linux OS: There are only two SQL Server drivers for UnixODBC: Easysoft (driver='Easysoft ODBC-SQL Server') and FreeTDS
				}
			}
			if ($drv!=='') $str .= ';DRIVER='.$drv; // Optional if a DSN is defined.
		} */
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
	
	function _Dbs_RsOpen($Src,$Sql) {
	// $Src is only for compatibility with TinyButStrong
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
		$this->_Message("This version of TbsSQL for ODBC (any database support) doesn't allow to use the method LastRowId() because there is no ODBC canonical function to perform this feature. You have to use the SQL function of the database instead.");
	}
	
	function _Dbs_AffectedRows() {
		$this->_Message("This version of TbsSQL for ODBC (any database support) doesn't allow to use the method AffectedRows() because there is no ODBC canonical function to perform this feature. You have to use the SQL function of the database instead.");
		$this->_Message("");
	}

}



?>