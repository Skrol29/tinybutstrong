<?php

/* Specific functions for the TinyButStrong plugin, can feet to the Drupal CMS.
Version 2011-01-05
*/

function tbs_plugin_SqlDbInit(&$TBS) {
// http://api.drupal.org/api/drupal/developer--globals.php/6
// in Drupal, global variable $active_db is an instance of class mysqli
	
	global $db_url, $active_db;
	$TBS->_CmsDbSrc =& $active_db; // used by MergeBlock tags
	$TBS->_CmsDbConfigName = basename($db_url);
	$TBS->_CmsDbActiveName = $TBS->_CmsDbConfigName;

}

function tbs_plugin_SqlDbId(&$DbSrc) {
// used by MergeBlock tags
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
// http://api.drupal.org/api/drupal/includes--database.mysql-common.inc/function/db_query/6
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
// http://api.drupal.org/api/drupal/includes--bootstrap.inc/function/variable_get/6
	return variable_get($OptName, $DefaultValue);
}

// database plug-in for TBS
function tbsdb_mysqli_open(&$Source, &$Query) {
	$Rs = $Source->query($Query); // returns false if an error occurs
	return $Rs;
}
function tbsdb_mysqli_fetch(&$Rs) {
	$Rec = $Rs->fetch_array();
	if (is_null($Rec)) $Rec = false;
	return $Rec;
}
function tbsdb_mysqli_close(&$Rs) {
	$Rs->free_result();
}
