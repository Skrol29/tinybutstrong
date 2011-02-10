<?php

/* Specific functions for the TinyButStrong plugin, can feet to the Joomla CMS.
Version 2011-02-10
*/

function tbs_plugin_SqlDbInit(&$TBS) {

	$TBS->_CmsDbSrc = &JFactory::getDBO();

	$conf = &JFactory::getConfig();
	$TBS->_CmsDbConfigName = $conf->getValue('db');

	$TBS->_CmsDbActiveName = $TBS->_CmsDbConfigName;

}

function tbs_plugin_SqlDbId(&$DbSrc) {
	// tbs_plugin_SqlDbId($TBS->_CmsDbSrc) is called for Direct-MergeBlock in "tinybutstrong_comm.php"
	if (version_compare(JVERSION,'1.6.0','<')) {
		return $DbSrc->_resource; // Joomla 1.5
	} else {
		return $DbSrc->getConnection(); // Joomla >= 1.6
	}
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
	$DBO = &JFactory::getDBO();
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
		$plugin = &JPluginHelper::getPlugin('content', 'tinybutstrong');
		if (version_compare(JVERSION,'1.6.0','<')) {
			$pluginParams = new JParameter($plugin->params); // Joomla = 1.5
		} else {
			$pluginParams = new JRegistry($plugin->params); // Joomla >= 1.6
		}
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
