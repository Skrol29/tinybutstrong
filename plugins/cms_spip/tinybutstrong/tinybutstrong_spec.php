<?php

/* Specific functions for the TinyButStrong plugin, can feet to the SPIP CMS.
Version 2009-08-20
*/

function tbs_plugin_SqlDbInit(&$TBS) {

	$TBS->_CmsDbSrc = false;
	
	$cnx = $GLOBALS['connexions'];
	
	$TBS->_CmsDbConfigName = $cnx[0]['db'];

	$TBS->_CmsDbActiveName = $TBS->_CmsDbConfigName;
}

function tbs_plugin_SqlDbId(&$DbSrc) {
	return 'mysql';
}

function tbs_plugin_SqlDbChange(&$DbSrc, $NewDbName) {
	return spip_mysql_selectdb($NewDbName);
}

function tbs_plugin_GetOption($OptName, $DefaultValue) {
	
	static $file = false;

	// Check for the option file, it is optional and absent by default.
	if ($file===false) {
		$file = dirname(__FILE__).'/tinybutstrong_options.php';
		if (file_exists($file)) {
			include($file);
		} else {
			$file = '';	
		}
	}
	
	// retrieve the defined option if any
	if ($file==='') {
		return $DefaultValue;
	} else {
		global $tbs_options;
		if (isset($tbs_options) and isset($tbs_options[$OptName])) {
			return $tbs_options[$OptName];
		} else {
			return $DefaultValue;
		}
	}
	
}

function tbs_plugin_SplitPage($page) {
// Specific to the SPIP CMS.

	$div_info = tbs_plugin_GetOption('ArtDiv',array('texte entry-content','texte"'));
	if (is_string($div_info)) $div_info = array($div_info);
	$div_beg = '<div';
	$div_end = '</div>';
	
	$TBS = false; // only to prepare error messages
	
	$p1 = false;
	foreach ($div_info as $di) {
		if ($p1===false) $p1 = strpos($page, $di);
	}
	if ($p1===false) {
		tbs_plugin_AddError($TBS, "The <div> tag that should begin the article content is not found at all. Check the plugin option ArtDiv, it should be corresponding with your template.");
		return false;
	}

	$p1 = strrpos(substr($page,0,$p1), '<');
	if ($p1===false) {
		tbs_plugin_AddError($TBS, "The <div> tag that should begin the article content is not correclty found. Check the plugin option ArtDiv, it should be corresponding with your template.");
		return false;
	}

	$p2 = $p1;
	$continue = true;
	while ($continue) {
		$p2 = strpos($page, $div_end, $p2+1);
		if ($p2===false) {
			tbs_plugin_AddError($TBS, "The </div> tag that should close the article content is not found. Check the plugin option ArtDiv.");
			return false;
		} else {
			$p2 = $p2 + strlen($div_end);
			$x = substr($page, $p1, $p2-$p1);
			if (substr_count($x,$div_beg)<=substr_count($x,$div_end)) {
				$x = str_replace(array('&nbsp;;','&#8217;'),array(';','\''), $x);
				$continue = false;
			}
		}
	}
	
	return array( substr($page,0,$p1), $x, substr($page,$p2) );
	
}


