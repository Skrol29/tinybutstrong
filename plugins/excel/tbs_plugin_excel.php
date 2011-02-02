<?php

/*
********************************************************
TinyButStrong plug-in: Excel Worksheets
Version 1.0.3, on 2006-07-11, by Skrol29
Version 1.1.0, on 2010-12-12, by Skrol29, for TBS version >= 3.6.2 
********************************************************
fixes:
[ok] Warning:  Parameter 4 to clsTbsExcel::BeforeMergeBlock() expected to be a reference, value given [...]
[ok] Strict Standards: call_user_func() expects parameter 1 to be a valid callback, non-static method clsTbsExcel::f_XmlConv() should not be called statically in ...\tbs_class.php on line 2411
[tt] add possibility to save the contents in a local file
[tt] avoid the download if a PHP is displayed (otherwise the contents is cut)
[  ] check cell types with fields not in a block (onload+onshow)
*/

// Name of the class is a keyword used for Plug-In authentication. So i'ts better to save it into a constant.
define('TBS_EXCEL','clsTbsExcel');
define('TBS_EXCEL_FILENAME',-1); // deprecated command (=1)
define('TBS_EXCEL_DOWNLOAD',1); // deprecated command (=2) & Render option (by default) = TBS_OUTPUT
define('TBS_EXCEL_INLINE',4);   // deprecated command (=3) & Render option (do not use value 2 wich is reserved for TBS_EXIT)
define('TBS_EXCEL_FILE',8);     // Render option
define('TBS_EXCEL_STRING',16);   // Render option

class clsTbsExcel {

	// TBS events -----------------------------

	function OnInstall() {
		$this->Version = '1.1.0-dev'; // Version can be displayed using [onshow..tbs_info] since TBS 3.2.0
		// Constants for the plug-in
		$this->TypeLst = array('xlNum'=>'Number', 'xlDT'=>'DateTime', 'xlStr'=>'String', 'xlErr'=>'Error', 'xlBoo'=>'Boolean');
		// Usefulle properies
		$this->FileName = '';
		$this->TemplateFileName = '';
		$this->ForceDownload = true;
		return array('OnCommand','BeforeLoadTemplate','AfterShow','OnOperation','BeforeMergeBlock','AfterMergeBlock','OnCacheField');
	}

	function OnCommand($Cmd,$Value='') {
		if ($Cmd==TBS_EXCEL_FILENAME) {
			// Change file name
			$this->FileName = $Value;
		} elseif ($Cmd==TBS_EXCEL_DOWNLOAD) {
			// Force output to download file
			$this->ForceDownload = true;
		} elseif ($Cmd==TBS_EXCEL_INLINE) {
			// Enables output to display inline (Internet Exlorer only)
			$this->ForceDownload = false;
		}
	}

	function BeforeLoadTemplate(&$File,&$HtmlCharSet) {
		if ($this->TemplateFileName==='') $this->TemplateFileName = $File;
		if ($HtmlCharSet==='') $this->TBS->LoadTemplate('', array(&$this,'ConvXmlUtf8')); // Define the function for string conversion
	}

	function OnCacheField($BlockName,&$Loc,&$Txt,$PrmProc) {

		if (isset($Loc->PrmLst['ope'])) {
			// in this event, ope parameter is not exploded yet
			$ope_lst = explode(',', $Loc->PrmLst['ope']);
			$ope_ok = false;
			foreach ($ope_lst as $ope) {
				$ope=trim($ope,' ');
				if (isset($this->TypeLst[$ope])) $ope_ok = $ope;
			}
			// if a the cell type must be modified, then we add a new TBS field for changing the attribute value
			if ($ope_ok!==false) $this->tag_ChangeCellType($Txt, $Loc, $ope_ok, $BlockName.'.#');
		}

	}

	function BeforeMergeBlock(&$TplSource,&$BlockBeg,&$BlockEnd,$PrmLst,&$DataSrc,&$LocR) {
	/* Manage attribute ssIndex.
	   Attribute ssIndex can be put by Excel on the first <Row> and <Cell> item of a <Table> element.
	   But this attribute must be put only once per series, it must not be repeated on next items when it is merged for a block.
	   So if we have it (it must be on the first item) we save it, delete it, and then and add it only for the first item merged (see AfterMergeBlock).
	*/
		$this->ssIndex = false;
		if ($LocR->SectionNbr>0) {
			$Src1 =& $LocR->SectionLst[1]->Src ;
			// Check if the first block begins with Row or Cell.
			$Start = '';
			if (substr($Src1,0,4)==='<Row')  $Start='<Row' ;
			if (substr($Src1,0,5)==='<Cell') $Start='<Cell' ;
			if ($Start!=='') {
				// The attribute ss:Index can be there.
				// Search for the end of the tag
				$p = strpos($Src1,'>');
				if ($p!==false) {
					$tag = substr($Src1,0,$p+1);
					// Searche for attribute ss:Index
					$p = strpos($tag,'ss:Index');
					if ($p!==false) {
						// ss:Index is found, we have to save it and take it off from the block's source
						$p1 = strpos($tag,'"',$p);
						if ($p1!==false) {
							$p2 = strpos($tag,'"',$p1+1);
							if ($p2!==false) {
								$len = $p2 - $p + 1;
								$this->ssIndex = true;
								$this->ssIndexStart = $Start;
								$this->ssIndexSource = substr($Src1,$p,$len);
								$Src1 = substr_replace($Src1,str_repeat(' ',$len),$p,$len);
							}
						}
					}
				}
			}
		}
	}

	function AfterMergeBlock(&$Buffer,&$DataSrc,&$LocR) {
		// Replace attribute ss:Index if necessary, only one time for the block.
		if ($this->ssIndex) {
			$len = strlen($this->ssIndexStart);
			if (substr($Buffer,0,$len)===$this->ssIndexStart) {
				$Buffer = substr_replace($Buffer,' '.$this->ssIndexSource,$len,0);
			}
			$this->ssIndex = false;
		}
	}

	function AfterShow(&$Render, $File='') {

		$TBS =& $this->TBS;
	
		// Delete optional XML attributes that could become invalide after the merge
		$this->tag_DelOptionalTableAtt();

		if ( ($TBS->ErrCount>0) && (!$TBS->NoErr) ) {
			$TBS->meth_Misc_Alert('Show() Method', 'The output is cancelled by the Excel plugin because at least one TBS error has occured.');
			exit;
		}
		
		if ($File==='') {
			if ($this->FileName==='') {
				$File = $this->f_DefaultFileName();
			} else {
				$File = $this->FileName;
			}
		}
		
		// Makes a download instead of displaying the result.
		if (($Render & TBS_OUTPUT)==TBS_OUTPUT) { // TBS_OUTPUT = TBS_EXCEL_DOWNLOAD
			// content downloaded
			$this->f_HttpHeader_Download($FileName);
		} elseif (($Render & TBS_EXCEL_INLINE)==TBS_EXCEL_INLINE) {
			// content displayed in the browser
			$this->f_HttpHeader_Inline($FileName);
			$Render = $Render - TBS_EXCEL_INLINE;
			if (($Render & TBS_OUTPUT)!=TBS_OUTPUT) $Render = $Render + TBS_OUTPUT; // needed for final output
		} elseif (($Render & TBS_EXCEL_FILE)==TBS_EXCEL_FILE) {
			// content saved in a file
			$hndl = fopen($File, 'w');
			fwrite($hndl, $this->TBS->Source);
			fclose($hndl);
		} elseif (($Render & TBS_EXCEL_STRING)==TBS_EXCEL_STRING) {
			// content returned in the TBS->Source property (nothing to do)
		}

		// the TBS::Show() method will then recover the process and perform the TBS_OUTPUT and TBS_EXIT options, if any
		
	}

	function OnOperation($FieldName,&$Value,&$PrmLst,&$Source,$PosBeg,$PosEnd,$Loc) {
		if (isset($this->TypeLst[$PrmLst['ope']])) {
			if (!isset($Loc->xlType)) $this->tag_ChangeCellType($Source,$Loc,$PrmLst['ope'],'onshow');
		} elseif ($PrmLst['ope']==='xlPushRef') {
			$this->tag_ChangeFormula($Source,$Loc,$Value,true);
		}
	}

	// --------------------------
	// Functions for internal job
	// --------------------------

	function ConvXmlOnly($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
	  $x = htmlspecialchars($Txt);
	  if ($ConvBr) $this->ConvBr($x);
	  return $x;
	}

	function ConvXmlUtf8($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
	  $x = htmlspecialchars(utf8_encode($Txt));
	  if ($ConvBr) $this->ConvBr($x);
	  return $x;
	}
	
	function f_HttpHeader_Download($FileName) {

		header ('Pragma: no-cache');
		//  header ('Content-type: application/x-msexcel');
		header ('Content-Type: application/vnd.ms-excel');
		header ('Content-Disposition: attachment; filename="'.$FileName.'"');

		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer'); 

		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.strlen($this->TBS->Source)); 

	}

	function f_HttpHeader_Inline($FileName) {

		header('Content-Type: application/x-msexcel; name="'.$FileName.'"');
		header('Content-Disposition: inline; filename="'.$FileName.'"');
		
	}
	
	function f_DefaultFileName() {
		if ($this->TemplateFileName==='') {
			return 'worksheet.xml';
		} else {
			$File = $this->TemplateFileName;
			// Keep only the file name
			$Pos = strrpos($File,'/');
			if ($Pos===false) $Pos = strrpos($File,'\\');
			if ($Pos!==false) $File = substr($File,$Pos+1);
			/*
			// Change extention from .xml to .xls in order to have a proper opening file with Explorer. Useless with Office 2010.
			$Len = strlen($File);
			if ($Len>4) {
				$Ext = substr($File,$Len-4,4);
				if (strtolower($Ext)=='.xml') $File = substr($File,0,$Len-4).'.xls';
			}
			*/
			return $File;
		}
	}

	function tag_ChangeCellType(&$Txt,&$Loc,$Ope,$Block) {
		$attval = $this->TypeLst[$Ope];
		if ($attval==='DateTime') {
			$Loc->PrmLst['frm'] = 'yyyy-mm-ddThh:nn:ss';
			// activate the FRM mode
			$Loc->ConvMode = 0;
			$Loc->ConvProtect = false;
		}
		if ( ($Block==='onshow') && ((substr($Loc->FullName,0,4)=='var.') || ($Loc->FullName==='var')) ) $Block='var..version'; // var fields ar processed after onshow, and they must have a subname
		$newfield = '['.$Block.';att=Data#ss:Type;if 1=1;then '.$attval.']';
		$Txt = substr_replace($Txt, $newfield, $Loc->PosEnd+1,0);
		$Loc->xlType = true; // avoid a double process of such ope values
	}
	
	function tag_ChangeFormula(&$Txt,&$Loc,&$Value,$First) {
		// The process assumes that the TBS is embeded into a N("") function

		if (isset($Loc->xlExend)) {
			
			if (!$Loc->xlExend) return false;
			
		} else {
			
			$Loc->xlExend = false;
			
			// So we first search for the begining of the TBS expression. Going backward.
			$p = $Loc->PosBeg - 1;
			$cont = true;
			$exp_n = false;   // true if N is met
			$exp_beg = false; //
			do {
				$x = $Txt[$p];
				if ($p<2) {
					$cont = false;
				} elseif ($x==='"') {
					$cont = false; // end of the formula
				} elseif ($x==='(') { // begining of the function's arguments 
					$exp_n = true;
				} elseif (($x==='+') or ($x==='&')) { // The expression can be added with + or & (wich is saved as &amp;)
					if ($exp_n)	{
						$exp_beg = $p;
						$cont = false;
					}
				}
				if ($cont) $p--;
			} while ($cont);
			if ($exp_beg===false) return false;
		
			// Search for the end of the TBS expression.
			$frm_end = strpos($Txt,'"',$Loc->PosEnd+1);
			if ($frm_end===false) return false;
			$exp_end = strpos($Txt,')',$Loc->PosEnd+1);
			if ($exp_end===false) return false;
			if ($exp_end>$frm_end) return false;
		
			// Now we searching backard, the relative cell	
			$cont = true;
			$br_o = false;    // position of [
			$br_c = false;    // position of ]
			$p = $exp_beg - 1;
			do {
				$x = $Txt[$p];
				if ($p<2) {
					$cont = false;
				} elseif ($x===']') {
					if ($br_o===false) $br_c = $p;
				} elseif ($x==='[') {
					if ($br_c!==false) $br_o = $p;
					$cont=false;
				} elseif ($x==='"') {
					$cont=false;
				}
				if ($cont) $p--;
			} while ($cont);
			if ($br_o===false) return false;
			
			// Calculate the relative index
			$x = intval(substr($Txt,$br_o+1,$br_c-$br_o-1));
			if ($x==0) return false;

			// Save information in the locator, useful for cached locators
			$Loc->xlVal = $x;
		  $Loc->xlExtraStr = substr($Txt,$br_c,$exp_beg-$br_c);
			$Loc->xlExend = true;
			$Loc->PosBeg = $br_o + 1;
			$Loc->PosEnd = $exp_end;

			$Loc->ConvMode = -1; // No contents conversion

		}
		
		// Calculate the new index
		$v = intval($Value);
		$x = $Loc->xlVal;
		if ($x!=0) $x = $x + $v - 1;
		
		// Replace the index value
		$Value = strval($x).$Loc->xlExtraStr;
	  
	}

	function tag_DelOptionalTableAtt() {
	// Two Table attributes give the size of the zone, this zone may be extended after a MergeBlock()
	// Hopefully, those attributes are optional, so we delete them.
		$Txt =& $this->TBS->Source;
		$att_lst = array('ss:ExpandedColumnCount', 'ss:ExpandedRowCount');
		$Pos = 0;
		while ( ($Loc=clsTinyButStrong::f_Xml_FindTag($Txt,'Table',true,$Pos,true,false,true,true))!==false ) {
			foreach ($att_lst as $att) {
				// delete the attributes by replacing them with spaces
				$att = strtolower($att); // The TBS method does turn attributes anmes into lowercase. 
				if (isset($Loc->PrmPos[$att])) {
					$p = $Loc->PrmPos[$att];
					$n = $p[3] - $p[0] + 1;
					$Txt = substr_replace($Txt, str_repeat(' ',$n), $p[0], $n);
				}
			}
			$Pos = $Loc->PosEnd+1;
		}

	}

}

