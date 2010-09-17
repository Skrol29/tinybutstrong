<?php

/* Common functions for the TinyButStrong plugin, can feet to any CMS.
Version 2009-12-14
*/

function tbs_plugin_Loop(&$TBS, $p1, $tag_beg, $tag_end) {
	
	$tag_lst = array();

	if ($p1===false) {
		$p1 = strpos($TBS->Source, $tag_beg); // if the first position is not yet set, we found it
		if ($p1===false) return $tag_lst;
	}
	
	do {

		$p2 = strpos($TBS->Source,$tag_end,$p1);
		if ($p2===false) {
			tbs_plugin_AddError($TBS, "one tag ".$tag_end." is missing after \"".substr($TBS->Source,$p1,16)."...\".");
			$p1 = false; // ends the loop
		} else {

			// Get tag's parameters
			$x = substr($TBS->Source,$p1+strlen($tag_beg),$p2-$p1-strlen($tag_beg));
			$x = explode(';',$x);
			$prm = array();
			foreach ($x as $px) {
				$p = strpos($px,'=');
				if ($p>0) {
					$prm[trim(substr($px,0,$p))] = trim(substr($px,$p+1));
				} else {
					$prm[trim($px)] = '';
				}
			}
			if (isset($prm['sql']) and (!isset($prm['sql_preserve']))) $prm['sql'] = str_replace(array('-+','+-'),array('<','>'),$prm['sql']);
			$tag_lst[] = $prm;
			
			// Delete tags in the article
			$TBS->Source = substr_replace($TBS->Source,'',$p1,$p2-$p1+strlen($tag_end));
			
			// Change the current database if necessary
			if (isset($prm['db'])) {
				if (($prm['db']=='%current%') or ($prm['db']=='%joomla%')) $prm['db'] = $TBS->_CmsDbConfigName; // %joomla% is supported for compatibility
				if ($TBS->_CmsDbActiveName!=$prm['db']) {
					if (tbs_plugin_SqlDbChange($DbSrc, $prm['db'])) {
						$TBS->_CmsDbActiveName = $prm['db'];
					} else {
						tbs_plugin_AddError($TBS, "cannot connect to database '".$prm['db']."'");
					}
				}
			}

			// Direct MergeBlock
			if (isset($prm['mergeblock']) and isset($prm['sql'])) {
				if (!isset($Opt_MergeBlock)) $Opt_MergeBlock = tbs_plugin_GetOption('MergeBlock','select');
				$ok = true;
				if ($Opt_MergeBlock=='select') {
					$x = $prm['sql'];
					$x = str_replace("\r",' ',$x);
					$x = str_replace("\n",' ',$x);
					$x = trim($x);
					if (strtolower(substr($x,0,7))!='select ') {
						tbs_plugin_AddError($TBS, "Only SELECT queries are allowed. See plugin configuration.");
						$ok = false;
					}
				} elseif ($Opt_MergeBlock!='all') {
					tbs_plugin_AddError($TBS, "parameter 'mergeblock' is not allowed. See plugin configuration.");
					$ok = false;
				}
				if ($ok) {
					tbs_plugin_LoadTemplate($TBS);
					$TBS->MergeBlock($prm['mergeblock'],tbs_plugin_SqlDbId($TBS->_CmsDbSrc),$prm['sql']);
				}
			}
			
			// External script
			if (isset($prm['script'])) {
				// Get the script path
				if (!isset($Opt_ScriptPath)) {
					$Opt_ScriptPath = trim(tbs_plugin_GetOption('ScriptPath', ''));
					if ($Opt_ScriptPath!='') {
						$x = substr($Opt_ScriptPath,-1);
						if (($x!=='\\') and ($x!=='/')) $Opt_ScriptPath .= DIRECTORY_SEPARATOR;
					}
					$Opt_Script = trim(tbs_plugin_GetOption('Script', 'loc'));
				}
				// Run the script
				if ($Opt_Script=='all') {
					if (file_exists($prm['script'])) {
						tbs_plugin_CallScript($TBS, $prm, $prm['script']);
					} else {
						tbs_plugin_CallScript($TBS, $prm, $Opt_ScriptPath.$prm['script']);
					}
				} elseif ($Opt_Script=='loc') {
					tbs_plugin_CallScript($TBS, $prm, $Opt_ScriptPath.$prm['script']);
				} else {
					tbs_plugin_AddError($TBS, "parameter 'script' is not allowed. See plugin configuration.");
				}
			}

			// Embedded script
			if (isset($prm['embedded'])) {
				// Check if this parameter is allowed
				if (!isset($Opt_Embedded)) $Opt_Embedded = tbs_plugin_GetOption('Embedded', 'no');
				if ($Opt_Embedded=='all') {
					$t1 = '<!--TBS';
					$t2 = '-->';
					$tp1 = -1;
					do {
						$tp1 = strpos($TBS->Source,$t1,$tp1+1);
						if ($tp1!==false) {
							$tp2 = strpos($TBS->Source,$t2,$tp1);
							if ($tp2===false) {
								tbs_plugin_AddError($TBS, "one tag '-->' is missing after '<!--TBS'.");
								$tp1 = false;
							} else {
								$script = substr($TBS->Source,$tp1+strlen($t1),$tp2-$tp1-strlen($t1));
								$script = trim($script);
								$TBS->Source = substr_replace($TBS->Source,'',$tp1,$tp2-$tp1+strlen($t2));
								tbs_plugin_LoadTemplate($TBS);
								eval($script);
							}
						}
					} while ($tp1!==false);
				} else {
					tbs_plugin_AddError($TBS, "parameter 'embedded' is not allowed. See plugin configuration.");
				}
			}

			$p1 = strpos($TBS->Source,$tag_beg);
			
		}
		
		
	} while ($p1!==false);

	return $tag_lst;

}

function tbs_plugin_IsIdIncluded($Lst,$Id) {
	if ($Lst=='') return false;
	$Lst = str_replace(' ',',',$Lst);
	$Lst = str_replace(';',',',$Lst);
	$Lst = ','.$Lst.',';
	if (strpos($Lst,',*,')!==false) return true;
	return (strpos($Lst,','.$Id.',')!==false);
}
	
function tbs_plugin_CallScript(&$TBS,$PrmLst,$Script) {
	tbs_plugin_LoadTemplate($TBS);
	// Call the script without access to local variables of the main method, but with variable $TBS and $PrmLst
	include($Script);
}

function tbs_plugin_LoadTemplate(&$TBS) {
// Load the template only once.	
	if ($TBS->_CmsTemplateLoaded) return;
	$TBS->LoadTemplate(null); // merge onload fields
	$TBS->_CmsTemplateLoaded = true; 
}

function tbs_plugin_AddError(&$TBS, $ErrMsg) {
	$Txt = "<font color=\"#FF0000\">TBS plugin error: ".htmlspecialchars($ErrMsg)."</font><br />\r\n";
	if ($TBS===false) {
		echo $Txt;
	} else {
		$TBS->Source = $Txt.$TBS->Source;
	}
}

function tbs_plugin_CheckArticle($ArtId) {
	// Check allowed ids
	$Opt_FullRightsIds = tbs_plugin_GetOption('FullRightsIds','');
	$FullRights = tbs_plugin_IsIdIncluded($Opt_FullRightsIds, $ArtId);
	if (!$FullRights) {
		$Opt_AllowedIds = tbs_plugin_GetOption('AllowedIds','');
		if (!tbs_plugin_IsIdIncluded($Opt_AllowedIds, $ArtId)) {
			$TBS = false;
			tbs_plugin_AddError($TBS, "this article is not allowed to use the TBS plugin. See TBS plugin parameters to allow this article.");
			return false;
		}
	}
	return true;
}

function tbs_plugin_InitTBS(&$TBS, $ArtText) {

	if (version_compare(PHP_VERSION,'5.0')<0) {
		include_once('tinybutstrong_class_php4.php');
	} else {
		include_once('tinybutstrong_class_php5.php');
	}

	$Opt_ChrBeg = tbs_plugin_GetOption('ChrBeg', '');
	$Opt_ChrEnd = tbs_plugin_GetOption('ChrEnd', '');
	if ($Opt_ChrBeg=='') $Opt_ChrBeg = '[';
	if ($Opt_ChrEnd=='') $Opt_ChrEnd = ']';
	$Opt_VarPrefix = tbs_plugin_GetOption('VarPrefix', '');

	$TBS = new clsTinyButStrong($Opt_ChrBeg.','.$Opt_ChrEnd, $Opt_VarPrefix);
	$TBS->Render = TBS_NOTHING;
	$TBS->Source = $ArtText;
	$TBS->_CmsTemplateLoaded = false; // custom property

}

function tbs_plugin_EndTBS(&$TBS) {
	
	if ($TBS->_CmsTemplateLoaded) $TBS->Show();
	
	if ($TBS->_CmsDbActiveName!=$TBS->_CmsDbConfigName) {
		tbs_plugin_SqlDbChange($TBS->_CmsDbSrc, $TBS->_CmsDbConfigName);
	}
	
	return $TBS->Source;
	
}