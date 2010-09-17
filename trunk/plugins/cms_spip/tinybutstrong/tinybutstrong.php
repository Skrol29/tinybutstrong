<?php

/* This is a SPIP plugin to produce dynamic articles.
Version 2009-08-12

The plugin is based on the "affichage_final" pipeline.
I've studied a way to base the plugin on the "pre_liens" pipeline because at this point
the article is fresh from the database and characters ';' are replaced with '&nbsp;;' juste after it.
But is was not helpfull because the articl was was cached anyway, even if its time life was zero ($GLOBALS['delais'] = 0).
And also because "pre_liens" is called sevarl time, and twice for the article when it is cached.
*/

function tbs_plugin_run($page) {

	// If the page is not an article, then the plugin do nothing.
	if (!isset($GLOBALS['contexte']['id_article'])) { // SPIP 2
		if (!isset($GLOBALS['page']['contexte']['id_article'])) { // SPIP 1.9.2
			return $page;
		} else {
			$ArtId = $GLOBALS['page']['contexte']['id_article'];
			$SpipType = '1.9'; // To be used in the option file if needed.
		}
	} else {
		$ArtId = $GLOBALS['contexte']['id_article'];
		$SpipType = '2'; // To be used in the option file if needed.
	}

	// Search for plugin's tags inside the article. There is a loop because the article can support several mergings.
	$tag_beg = '[tbs]';
	$tag_end = '[/tbs]';
	$p1 = strpos($page, $tag_beg);
	if ($p1===false) return $page; // If no TBS tag, then the article doesn't need the plugin.

	include_once('tinybutstrong_comm.php');
	include_once('tinybutstrong_spec.php');

	$TBS = false;
	
	// Exit the plugin if the article is not allowed
	if (!tbs_plugin_CheckArticle($ArtId)) return $page;
	
	// SPIP add a non-breakable-space before each ';' in the articles, so we have to fix that but only for the article snippet.
	$page_parts = tbs_plugin_SplitPage($page);
	if ($page_parts===false) return $page; // an error has occured
	
	tbs_plugin_InitTBS($TBS, $page_parts[1]);

	tbs_plugin_SqlDbInit($TBS); // retrieve SQL information and save them as custom properties of $TBS
	
	// Note: we canot re-use $p1 for SPIP because the begin of the article is set in the content header.
	$tag_lst = tbs_plugin_Loop($TBS, false, $tag_beg, $tag_end); // Search for all TBS tags and process them.

	$page_parts[1] = tbs_plugin_EndTBS($TBS); // ends the merge.
	return $page_parts[0].$page_parts[1].$page_parts[2];

}
	
?>