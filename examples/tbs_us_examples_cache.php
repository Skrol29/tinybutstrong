<?php

include_once('tbs_class.php');
$TBS = new clsTinyButStrong;

include_once('tbs_plugin_cache.php');   // Load the Cache System library
$TBS->PlugIn(TBS_INSTALL, TBS_CACHE, dirname(__FILE__)); // Install the plug-in

// Call the Cache System which is deciding wheter to continue and store the result into a cache file, or to display a cached page.
if ($TBS->PlugIn(TBS_CACHE,'testcache',10)) {
 } else {
	$TBS->LoadTemplate('tbs_us_examples_cache.htm');
	$TBS->Show();
}
	
?>