<?php

/* Specific functions for the TinyButStrong plugin, can feet to the Joomla CMS.
Version 2009-08-17
*/

function tbs_plugin_SqlDbInit(&$TBS) {

	$TBS->_CmsDbSrc =& JFactory::getDBO();

	$conf =& JFactory::getConfig();
	$TBS->_CmsDbConfigName = $conf->getValue('db');

	$TBS->_CmsDbActiveName = $TBS->_CmsDbConfigName;

}

function tbs_plugin_SqlDbId(&$DbSrc) {
	return $DbSrc->_resource;
}

function tbs_plugin_SqlDbChange(&$DbSrc, $NewDbName) {
	return $DbSrc->select($NewDbName);
}

function tbs_plugin_SqlGetRows($SQL,$ErrMsg='') {
// Not used in the plugin, this is a wrapper for developpers
	return tbs_plugin_SqlExecute($SQL, $ErrMsg, true);
}

function tbs_plugin_SqlExecute($SQL,$ErrMsg='',$getRows=false) {
// Not used in the plugin, this is a wrapper for developpers
	$DBO =& JFactory::getDBO();
	$DBO->setQuery($SQL);
	$DBO->query();
	if ($DBO->getErrorMsg()=='') {
		if ($getRows) {
			return $DBO->loadAssocList();
		} else {
			return true;
		}
	} else {
		$TBS = false;
		tbs_plugin_AddError($TBS, $ErrMsg." ".$DBO->getErrorMsg());
		return false;
	}
}
	
function tbs_plugin_GetOption($OptName, $DefaultValue) {
	static $pluginParams = false;
	if ($pluginParams===false) {
		$plugin = & JPluginHelper::getPlugin('content', 'tinybutstrong');
		$pluginParams = new JParameter($plugin->params);
	}
	$OptValue = $pluginParams->def($OptName,$DefaultValue);
	return $OptValue;
}

if (!function_exists('tbsdb_mysqli_open')) {
	// Database TBS plugin for MySQLi
	function tbsdb_mysqli_open(&$Source, &$Query) {
		$Rs = @mysqli_query($Source, $Query);
		if (!$Rs) {
			echo '<font color="#FF0000">TinyButStrong functions for MySQLi: '.htmlentities(mysqli_error($Source)).". SQL Source : {".$Query."}</font><br />\r\n";
			$Rs = false;
		}
		return $Rs;
	}
	function tbsdb_mysqli_fetch(&$Rs) {
		$Rec = mysqli_fetch_assoc($Rs);
		if (is_null($Rec)) $Rec = false;
		return $Rec;
	}
	function tbsdb_mysqli_close(&$Rs) {
		mysqli_free_result($Rs);
	}
}
