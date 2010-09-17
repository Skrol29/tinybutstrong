<?php

/* Specific functions for the TinyButStrong plugin, can feet to the Joomla CMS.
Version 2010-05-20
*/

function tbs_plugin_SqlDbInit(&$TBS) {

	// global variable $active_db is an instance of class mysqli
	
	global $db_url, $active_db;

	$TBS->_CmsDbSrc =& $active_db;
	$TBS->_CmsDbConfigName = basename($db_url);
	$TBS->_CmsDbActiveName = $TBS->_CmsDbConfigName;

}

function tbs_plugin_SqlDbId(&$DbSrc) {
	return $DbSrc;
}

function tbs_plugin_SqlDbChange(&$DbSrc, $NewDbName) {
	return $DbSrc->select_db($NewDbName);
}

function tbs_plugin_SqlGetRows($SQL,$ErrMsg='') {
// Not used in the plugin, this is a wrapper for developpers
	return tbs_plugin_SqlExecute($SQL, $ErrMsg, true);
}

function tbs_plugin_SqlExecute($SQL,$ErrMsg='',$getRows=false) {
// Not used in the plugin, this is a wrapper for developpers
	$result = db_query($SQL);
	if ($result!==false) {
		if ($getRows) {
			return $result;
		} else {
			return true;
		}
	} else {
		$TBS = false;
		$db_err = "Database error";
		tbs_plugin_AddError($TBS, $ErrMsg." ".$db_err);
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
