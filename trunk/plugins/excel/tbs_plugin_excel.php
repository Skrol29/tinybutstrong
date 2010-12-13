<?php

/*
********************************************************
TinyButStrong plug-in: Excel Worksheets
Version 1.0.3, on 2006-07-11, by Skrol29
Version 1.0.4, on 2010-12-12, by Skrol29
********************************************************
fixes:
Warning:  Parameter 4 to clsTbsExcel::BeforeMergeBlock() expected to be a reference, value given [...]

*/

// Name of the class is a keyword used for Plug-In authentication. So i'ts better to save it into a constant.
define('TBS_EXCEL','clsTbsExcel');
define('TBS_EXCEL_FILENAME',1);
define('TBS_EXCEL_DOWNLOAD',2);
define('TBS_EXCEL_INLINE',3);

class clsTbsExcel {

	// TBS events -----------------------------

	function OnInstall() {
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

		if (isset($this->zzz)) {
			$this->zzz++;
		} else {
			$this->zzz = 1;
		}
		echo "* $BlockName . ".$Loc->SubName ." <br>\r\n";
		echo "* src=\r\n".$Txt."\r\n";
		if ($this->zzz>=100) exit;
		
		if (isset($Loc->PrmLst['ope'])) {
		  // in this event, ope is not exploded yet
			$ope_lst = explode(',', $Loc->PrmLst['ope']);
			$attval = false;
			foreach ($ope_lst as $ope) {
				if (isset($this->TypeLst[$ope])) $attval = $this->TypeLst[$ope];
			}
			// if a the cell type must be modified, then we add a new TBS field for changing the attribute value
			if ($attval!==false) {
				$frm = ($attval==='DateTime') ? ";frm='yyyy-mm-ddThh:nn:ss'" : '';
				$newfield = '['.$BlockName.'.#;att=Data#ss:Type;if 1=1;then '.$attval.$frm.']';
				$Txt = substr_replace($Txt, $newfield, $Loc->PosEnd+1,0);
				//echo $Txt; exit;
				$Loc->xlType = true; // avoid a double process of such ope values
			}
		}

	}

	function BeforeMergeBlock(&$TplSource,&$BlockBeg,&$BlockEnd,$PrmLst,&$DataSrc,&$LocR) {

		// Manage attribute ssIndex.
		// Attribute ssIndex can be put by Excel on the first <Row> and <Cell> item of a <Table> element.
		// But this attribute must be put only once per series, it must not be repeated on next items when it is merged for a block.
		// So if we have it (it must be on the first item) we save it, delete it, and then and add it only for the first item merged (see AfterMergeBlock).
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

	function AfterShow(&$Render) {

		$TBS =& $this->TBS;
	
		// Delete optional XML attributes that could become invalide after the merge
		$this->tag_DelOptionalTableAtt();

		if ( ($TBS->ErrCount>0) && (!$TBS->NoErr) ) {
			$TBS->meth_Misc_Alert('Show() Method', 'The output is cancelled by the Excel plugin because at least one error has occured.');
			exit;
		}
		
		// Makes a download instead of displaying the result.
		if (($Render & TBS_OUTPUT)==TBS_OUTPUT) {
			$Render = $Render - TBS_OUTPUT;
			$FileName = $this->FileName;
			if ($FileName==='') $FileName = $this->f_DefaultFileName();
			$this->f_Display($FileName,$this->ForceDownload); // Output with header
		}

	}

	function OnOperation($FieldName,&$Value,&$PrmLst,&$Source,$PosBeg,$PosEnd,$Loc) {
		if (isset($this->TypeLst[$PrmLst['ope']])) {
			if (!isset($Loc->xlType)) $this->tag_ChangeCellType($Source,$Loc,$this->TypeLst[$PrmLst['ope']]);
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
	
	function f_XmlConv($x) {
	// Convertion of data items
		 return htmlspecialchars(utf8_encode($x));
	}

	function f_Display($FileName,$Download) {

		if ($Download) {
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
		} else {
			header('Content-Type: application/x-msexcel; name="'.$FileName.'"');
			header('Content-Disposition: inline; filename="'.$FileName.'"');
		}
		
		echo($this->TBS->Source);
		
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

	function tag_ChangeCellType(&$Txt,&$Loc,$NewType) {
	// Assuming that $Pos is the position of a value embedded into a <Data> tag with attribute ss:Type="String",
	// this function changes this attribute into ss:Type="Number".

		// Search the attribute's value delimited by (")
		$p = $Loc->PosBeg - 1; // We go backward
		$close = false;
		$cont = true;
		$val_beg = false;
		$val_end = false;
		do {
			$x = $Txt[$p];
			if ($x==='<') {
				$cont = false;
			} elseif ($x==='>') {
				$close = true;
			} elseif ($close and ($x==='"')) {
				if ($val_end===false) {
					$val_end = $p;
				} elseif (substr($Txt,$p-8,8)==='ss:Type=') {
					$val_beg = $p;
					$cont = false;
				}
			} elseif ($p<=8) {
				$cont = false;
			}
			if ($cont) $p--;
		} while ($cont);

		if ($val_beg===false) return false;
		
		// Replace old attribute's value by the new one
		$len1 = $val_end - $val_beg + 1;
		$x = '"'.$NewType.'"';
		$lenx = strlen($x);
		$delta = $lenx-$len1;
		if ($delta<0) {
			// And blancks in order to not move the TBS tag
			$x .= str_repeat(' ',-$delta);
		} elseif ($delta>0) {
			// Move the begining of the TBS tag, but not the end in order to let the following as is.
			// This works only if there is no more TBS tag between the Type attribute and the begining of the current TBS tag.
			$x .= substr($Txt,$val_end+1,$Loc->PosBeg-$val_end-1);
			$len1 = $Loc->PosBeg - $val_beg + $delta;
			$Loc->PosBeg += $delta;
		}
		$Txt = substr_replace($Txt,$x,$val_beg,$len1);

		// If DateTime => apply format
		if ( ($NewType==='DateTime') and (!isset($Loc->PrmLst['frm'])) ) {
			// We have to use parameter "frm" instead of chnaging the value because this function can be called for cached TBS tags.
			$Loc->PrmLst['frm'] = 'yyyy-mm-ddThh:nn:ss'; // Excel-XML datetime format
			// Below is for TBS tags currently being merged (like [var] fields).
			$Loc->ConvMode = 0; // Frm
			$Loc->ConvProtect = false;
		}
		return true;
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

