<?php

// TbsSql Engine
// Version 3.1, 2010-10-11, Skrol29
// www.tinybutstrong.com

if ( (version_compare(PHP_VERSION,'5')<0) && (!function_exists('clone'))  ) {
	eval('function clone($object) {return $object;}'); // eval is needed because the syntax function clone() is refused in PHP 5
}

define('TBSSQL_SILENT', 0);
define('TBSSQL_NORMAL', 1);
define('TBSSQL_DEBUG', 2);
define('TBSSQL_TRACE', 4);
define('TBSSQL_GRID', 8);
define('TBSSQL_CONSOLE', 16);
define('TBSSQL_1MINUTE', 1);
define('TBSSQL_1HOUR', 60);
define('TBSSQL_1DAY', 24*60);
define('TBSSQL_1WEEK', 7*24*60);
define('TBSSQL_NOCACHE', -1);
define('TBSSQL_DISABLED', false);
define('TBSSQL_ALWAYS', 0);
define('TBSSQL_ARRAY', 'array');
define('TBSSQL_OBJECT', 'object');

class clsTbsSql {

	function __construct($srv='',$uid='',$pwd='',$db='',$drv='',$Mode=TBSSQL_NORMAL) {
		// Default values (defined here to be compatible with both PHP 4 & 5)
		$this->Version = '3.1';
		$this->Id = false;
		$this->SqlNull = 'NULL'; // can be modified by user
		$this->DefaultRowType = TBSSQL_ARRAY;
		$this->CacheDir = '.';
		$this->CacheTimeout = false; // in minutes
		$this->TempCacheTimeout = false;
		$this->CacheAutoClear = TBSSQL_1WEEK; // 1 week in minutes
		$this->CacheSuffix = '';
		$this->InitMsg = true;
		$this->InitCsl = true;
		$this->_CacheCurrSql = false; // is different from false if data as to be saved
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
		$RsId = $this->_Dbs_RsOpen($Sql, true);
		if ($RsId===false) return $this->_SqlError($this->Id);
		$this->_Dbs_RsClose($RsId);
		return true;
	}

	function GetVal($Sql) {

		$ArgLst = func_get_args();
		$Sql = $this->_SqlProtect($ArgLst);

		$Data = array();
		if (!$this->_CacheTryRetrieve($Sql,$Data)) {
			if (!$this->_GetDataFromDb($Sql, $Data, ($this->_CacheCurrSql===false))) return false; // if CacheSql is false, then we do not need to retrieve all the records from the db
			if ($this->_CacheCurrSql!==false) $this->_CacheUpdate($Data);
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
			if (!$this->_GetDataFromDb($Sql, $Data, ($this->_CacheCurrSql===false))) return false; // if CacheSql is false, then we do not need to retrieve all the records from the db
			if ($this->_CacheCurrSql!==false) $this->_CacheUpdate($Data);
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
			if ($this->_CacheCurrSql!==false) $this->_CacheUpdate($Data);
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
			if ($this->_CacheCurrSql===false) {
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

	function LastRowId($Info=false) {
		return $this->_Dbs_LastRowId($Info);
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

	function CacheTimestamp($Sql) {
	// return the timestamp of the cache file
		$x = $this->_CacheFilePath($Sql);
		if (file_exists($x)) {
			return filemtime($x);
		} else {
			return false;
		}
	}

	function CacheDelete($Sql) {
	// delete the cache file
		$x = $this->_CacheFilePath($Sql);
		if (file_exists($x)) {
			$ok = unlink($x);
			if ($ok) {
				if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: CacheDelete() has deleted the cache file: '.$x, '#060');
			} else {
				$this->_Message('[Error]: CacheDelete() unexpected failure. Check permissions for deleting cache file '.$x);
			}
			return $ok;
		} else {
			if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: CacheDelete() has not found the cache file '.$x, '#060');
			return false;
		}
	}

	function CacheFile($Sql) {
	// return the full path of the cache file
		$x = $this->_CacheFilePath($Sql);
		if (file_exists($x)) {
			return $x;
		} else {
			return false;
		}
	}

	function ConfInfo() {
		
		$this->InitMsg = false; // prevent from displaying the info twice
		
		// version
		$this->_Message('[Configuration]: TbsSql version '.$this->Version, '#060');
		
		// mode
		$x = $this->_ModeDecompose($this->Mode, array(TBSSQL_SILENT=>'TBSSQL_SILENT', TBSSQL_NORMAL=>'TBSSQL_NORMAL', TBSSQL_DEBUG=>'TBSSQL_DEBUG', TBSSQL_TRACE=>'TBSSQL_TRACE', TBSSQL_GRID=>'TBSSQL_GRID', TBSSQL_CONSOLE=>'TBSSQL_CONSOLE'));
		$this->_Message('[Configuration]: property Mode = '.$x, '#060');
		
		// DefaultRowType
		$x = $this->_ModeDecompose($this->DefaultRowType, array(TBSSQL_ARRAY=>'TBSSQL_ARRAY', TBSSQL_OBJECT=>'TBSSQL_OBJECT'));
		$this->_Message('[Configuration]: property DefaultRowType = '.$x, '#060');

		// CacheTimeout
		$x = $this->_ModeDecompose($this->CacheTimeout, false);
		$this->_Message('[Configuration]: property CacheTimeout = '.$x, '#060');

		// TempCacheTimeout
		$x = $this->_ModeDecompose($this->TempCacheTimeout, false);
		$this->_Message('[Configuration]: property TempCacheTimeout = '.$x, '#060');

		// CacheAutoClear
		$x = $this->_ModeDecompose($this->CacheAutoClear, false);
		$this->_Message('[Configuration]: property CacheAutoClear = '.$x, '#060');

		// CacheDir and CacheSuffix
		$this->_Message('[Configuration]: property CacheDir = '.var_export($this->CacheDir,true), '#060');
		$this->_Message('[Configuration]: property CacheSuffix = '.var_export($this->CacheSuffix,true), '#060');
		
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
			if ($this->_CacheCurrSql===false) {
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
			if ($this->_CacheCurrSql!==false) $this->_CacheUpdate($this->_CacheTbsData);
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

	function _Message($Txt, $Color='#FF0000') {
	// display a message right now in the Html page or in the console. Must return false. Default color is red, which means error.

		if ($this->Mode===TBSSQL_SILENT) return false;

		if ($this->InitMsg) {
			// display the TbsSql version the very first time and continue
			$this->ConfInfo();
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
TbsSqlConsole.document.body.innerHTML = ""; // clear the console when the parent window is actualized
TbsSqlConsole.document.write(\'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>TbsSQL Console</title></head><body>'.$Html.'</body></html>\');
TbsSqlConsole.focus();
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

	function _ModeDecompose($Mode, $Options ) {
	// decompose the mode in options

		// string mode
		if (is_string($Mode)) {
			if (isset($Options[$Mode])) {
				return $Options[$Mode];
			} else {
				return $Mode;
			}
		}
		
		// duration
		if ($Options===false) {
			$Options = array('TBSSQL_1WEEK'=>TBSSQL_1WEEK, 'TBSSQL_1DAY'=>TBSSQL_1DAY, 'TBSSQL_1HOUR'=>TBSSQL_1HOUR, 'TBSSQL_1MINUTE'=>TBSSQL_1MINUTE, 'TBSSQL_ALWAYS'=>TBSSQL_ALWAYS, 'TBSSQL_NOCACHE'=>TBSSQL_NOCACHE, 'TBSSQL_DISABLED'=>TBSSQL_DISABLED); // order must be respected
			if (in_array($Mode, $Options, true)) return array_search($Mode, $Options, true);
			$x = array();
			$remain = $Mode;
			foreach ($Options as $name=>$duration) {
				if ( ($duration!==false) && ($duration>0) && ($remain>=$duration) ) {
					$r = ($remain % $duration);
					$n = intval(($remain-$r)/$duration);
					$remain = $remain - ($n*$duration);
					$x[] = ($n==1) ? $name : $n.'*'.$name;
				}
			}
			if (count($x)==0) return $Mode.' (minutes)';
			if ($remain>0) $x[] = $remain.' (minutes)';
			return implode(' + ', $x);
		}
		
		// numeric
		$x = array();
		$remain = $Mode;
		foreach ($Options as $opt=>$name) {
			if ( ($opt!=0) && (($Mode & $opt)===$opt) ) {
				$x[] = $name;
				$remain = $remain - $opt;
			}
		}
		if ($remain!=0) $x[] = $remain;
		if (count($x)==0) {
			if (isset($Options[0])) {
				$x[] = $Options[0];
			} else {
				$x[] = 0;
			} 
		}
		return implode(' + ', $x);
		
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

	function _SqlDateFrmDb($Date,$FrmMode) {
	// Prepare the date item before to merge it in an SQL query

		if (is_string($Date)) {
			if (!isset($this->_SqlDateParseOk)) $this->_SqlDateParseOk = function_exists('date_parse'); // date_parse() is available since PHP 5.2
			if ($this->_SqlDateParseOk) {
				$x = date_parse($Date);
				if (($x===false) || ($x['warning_count']>0) || ($x['error_count']>0)) {
					$this->_Message('[Error]: the date argument (string) \''.$Date.'\' is not recognized as a valide date. It will be merged as is in the SQL query.');
					$Mode = 0; // The date will be passed as is to the SQL
					$x = $Date;
				} else {
					$x = array( 'y'=>$x['year'] , 'm'=>$x['month'] , 'd'=>$x['day'] , 'h'=>intval($x['hour']) , 'i'=>intval($x['minute']) , 's'=>intval($x['second']), 'f'=>intval($x['fraction'])); // intval() helps to force false to 0
				}
			} else {
				$x = strtotime($Date);
				if (($x===-1) || ($x===false)) {
					$this->_Message('[Error]: the date argument (string) \''.$Date.'\' is not recognized as a valide date. This can happens on 32bit systems for dates over 2038-01-19. TbsSQL can workaround this date limit if you use PHP>=5.2');
					$Mode = 0; // The date will be passed as is to the SQL
					$x = $Date;
				} else {
					$x = $this->_SqlDateInfo($x);
				}
			}
		} elseif (is_int($Date) || is_float($Date)) {
			// It's a timestamp
			$x = $this->_SqlDateInfo($Date);
		} elseif (is_array($Date)) {
			// It's an array supported by TbsSQL
			$x = $Date;
			if (!isset($x['y'])) {
				$this->_Message('[Error]: the date argument is an array but has no key \'y\'. The value will be replaced with the current date-time.');
				$x = $this->_SqlDateInfo(time());
			}
			if (!isset($x['m'])) $x['m'] = 1;
			if (!isset($x['d'])) $x['d'] = 1;
			if (!isset($x['h'])) $x['h'] = 0;
			if (!isset($x['i'])) $x['i'] = 0;
			if (!isset($x['s'])) $x['s'] = 0;
		} else {
			$x = ''; // avoid a PHP error notice
			$this->_Message('[Error]: the date argument can not be recognized as date: '.var_export($Date,true));
		}

		return $this->_Dbs_Date($x,$FrmMode);

	}

	function _SqlDateInfo($Timestamp) {
	// return a date info array from a timestamp
		$x = getdate($Timestamp);
		return array( 'y'=>$x['year'] , 'm'=>$x['mon'] , 'd'=>$x['mday'] , 'h'=>$x['hours'] , 'i'=>$x['minutes'] , 's'=>$x['seconds'], 'f'=>0.0);
	}

	function _SqlDateFrmStd($d, $s_date, $s_ent=false, $s_time=false, $s_frac=false, $s_frac_nbr=3) {
	// useful function for common date formating, to be used in method _Dbs_Date();
		$x = $d['y'].$s_date.str_pad($d['m'],2,'0',STR_PAD_LEFT).$s_date.str_pad($d['d'],2,'0',STR_PAD_LEFT);
		if ($s_ent!==false) {
			// time is added to the date
			$x .= $s_ent.str_pad($d['h'],2,'0',STR_PAD_LEFT).$s_time.str_pad($d['i'],2,'0',STR_PAD_LEFT).$s_time.str_pad($d['s'],2,'0',STR_PAD_LEFT);
			if ($s_frac!==false) $x .= $s_frac.substr(number_format($d['f'],$s_frac_nbr,'.',''),2); // fraction of seconds is added to the date-time
		}
		return $x;
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
							$x = $x = $this->_SqlDateFrmDb($ArgLst[$i],1); // Date value
						} elseif ($Chr==='~') {
							$x = $x = $this->_SqlDateFrmDb($ArgLst[$i],2); // Date and time value
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
				$this->_Message('[SQL]: '.$Sql,'#663399');
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

	function _CacheFilePath($Sql) {
		return $this->CacheDir.'/cache_tbssql_'.md5($Sql).$this->CacheSuffix.'.php';  // we save it as a PHP file in order to hide the contents from web users
	}

	function _CacheTryRetrieve($Sql, &$Data) {
	// Try to retrieve data from the cache file. Return true if $Data contains the data from the cache.

		// at this point $this->_CacheCurrSql is always false
		$this->_CacheCurrSql = false; // for security

		// check if cache is enabled
		if ($this->TempCacheTimeout===false) {
			if (($this->CacheTimeout===false) || ($this->CacheTimeout===TBSSQL_NOCACHE)) return false;
			$timeout = $this->CacheTimeout;
		} else {
			$timeout = $this->TempCacheTimeout;
			$this->TempCacheTimeout = false;
			if ($timeout===TBSSQL_NOCACHE) return false;
		}

		// 
		$this->_CacheCurrFile = $this->_CacheFilePath($Sql);
		$now = time();
		//echo 'debug CacheTryRetrieve : timout= '.$timeout.', cache file='.date('Y-m-d h:i:s',@filemtime($this->_CacheCurrFile)).' , limit='.date('Y-m-d h:i:s',@filemtime($this->_CacheCurrFile)+60*$timeout).' , now='.date('Y-m-d h:i:s',$now)."<br>\r\n";
		if ( file_exists($this->_CacheCurrFile) && ($now<=(filemtime($this->_CacheCurrFile)+60*$timeout)) ) {
			// retrieve the data
			// echo 'debug CacheTryRetrieve : cache still current'."<br>\r\n";
			include($this->_CacheCurrFile); // set $CacheSql and $Data
			if ($Sql===$CacheSql) {
				if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: Data retrieved from cache file '.$this->_CacheCurrFile,'#060');
				if ($this->_ModeHas(TBSSQL_GRID))  $this->_Message($Data);
				return true;
			} else {
				// It can happens very rarely that two different SQL queries have the same md5, with this chech we are sure to have to good result
				if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: Data not retrieved from cache file '.$this->_CacheCurrFile.' because SQL is different.','#060');
				return false;
			}
		}
		
		//echo 'debug CacheTryRetrieve : cache to be updated.'."<br>\r\n";
		$this->_CacheCurrSql = $Sql;
		return false;
		
	}

	function _CacheUpdate($Data) {
	// Update the cache

		$fid = @fopen($this->_CacheCurrFile, 'w');
		if ($fid===false) {
			$this->_Message('[Error]: The cache file '.$this->_CacheCurrFile.' cannot be saved.');
			$ok = false;
		} else {
			flock($fid,2); // acquire an exlusive lock
			fwrite($fid,'<?php $CacheSql='.var_export($this->_CacheCurrSql,true).'; ');
			fwrite($fid,'$Data='.var_export($Data,true).';');
			flock($fid,3); // release the lock
			fclose($fid);
			//echo 'debug CacheTryUpdate : cache file='.date('Y-m-d h:i:s',filemtime($this->_CacheCurrFile)).' end='.date('Y-m-d h:i:s',$cache_end).' , now='.date('Y-m-d h:i:s',time()).' , ok='.var_export($ok,true)."<br>\r\n";
			if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: Data saved in cache file '.$this->_CacheCurrFile,'#060');
			$ok = true;
		}
		$this->_CacheCurrSql = false;
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

		if ($this->_ModeHas(TBSSQL_TRACE)) $this->_Message('[Cache]: CacheAutoClear has deleted '.count($lst).' old cache files from directory '.$this->CacheDir,'#060');

	}

// -------------------------------
// Specific to the Database System
// -------------------------------

// Database Engine: MySQL, using mysql functions
// Version 1.03, 2010-09-02, Skrol29

	function _Dbs_Prepare() {
		// If no connexion parameters are given, try to link to the current MySQL connection
		$this->Version .= ' for Oracle';
		
		$this->Php4 = ( version_compare(PHP_VERSION, '5') < 0 );
		$this->_RsId = false; // for managing rowcount
	}

	function _Dbs_Connect($srv,$uid,$pwd,$db,$drv) {
	// Should set $this->Id, value false means connection failed.

		if ($db!=='') $srv .= '/'.$db;

		// Information, must be the same for any database type	
		if ($this->Mode==TBSSQL_DEBUG) $this->LastSql = 'Connection String='.$srv;
		if ($this->Mode==TBSSQL_TRACE) $this->_Message('Trace Connection String: '.$srv,'#663399');

		$this->Id = oci_connect($uid,$pwd,$srv); // Force a new connection in order to manage connections to two different DB in the same script. Doesn't work if SQL Safe Mode is activated.

	}

	function _Dbs_Close() {
		if ($this->_RsId!==false) $this->_Dbs_RsCloseSpecial();
		if ($this->Php4) {
			return false;
		} else {
			return @oci_close($this->Id);
		}
	}

	function _Dbs_Error($ObjId) {
		if (($ObjId!==false) && is_resource($this->Id)) {
			return @oci_error($ObjId);
		} else {
			return @oci_error();
		}
	}

	function _Dbs_RsOpen($Sql, $Exec=false) {
		if ($this->Php4) {
			$RsId = ociparse($this->Id, $Sql);
		} else {
			$RsId = oci_parse($this->Id, $Sql);
		}
		$ok = oci_execute($RsId);
		
		if ($this->_RsId!==false) $this->_Dbs_RsCloseSpecial();
		if ($Exec) $this->_RsId = $RsId; // $RsId is stored in order to be callable for a _Dbs_AffectedRows()
		return $RsId;
		
	}

	function _Dbs_RsFetch(&$RsId) {
		return oci_fetch_assoc($RsId);
	}

	function _Dbs_RsClose(&$RsId) {
		if ($this->_RsId===false) {
			return oci_free_statement($RsId);
		} else {
			return true;
		}
	}

	function _Dbs_ProtectStr($Txt) {
		return str_replace('\'', '\'\'',$Txt);
	}

	function _Dbs_Date($DateInfo,$FrmMode) {
		switch ($FrmMode) {
		case 1:
			// Date only
			return "TO_DATE('".$this->_SqlDateFrmStd($DateInfo, '-')."','yyyy-mm-dd')";
		case 2:
			// Date and time
			return "TO_DATE('".$this->_SqlDateFrmStd($DateInfo, '-', ':')."','yyyy-mm-dd hh24:mi:ss')";
		case 0:
			// Value is a string
			return '\''.$this->_Dbs_ProtectStr($DateInfo).'\'';
		default:
			// Error in date recognization
			return '\'0000-00-00\'';
		}
	}

	function _Dbs_LastRowId($seq_name) {
		return $this->GetVal('SELECT '.$seq_name.'.currVal id FROM Dual'); // nextVal chnages the sequeance! currVal works only if nextVal has been call in the same cession
		
	}

	function _Dbs_AffectedRows() {
		if ($this->_RsId===false) {
			return 0;
		} else {
			return oci_num_rows($this->_RsId);
		}
	}

	function _Dbs_RsCloseSpecial() {
		$ok = oci_free_statement($this->_RsId);
		$this->_RsId = false;
		return $ok;
	}

}