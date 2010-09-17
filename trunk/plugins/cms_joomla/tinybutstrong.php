<?php
/* TBS Dynamic Articles, a content plugin for Joomla 1.5
Version 2009-12-14
This plugin is under the GPL license version 3.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

class plgContentTinyButStrong extends JPlugin
{
 /**
  * Constructor
  *
  * For php4 compatability we must not use the __constructor as a constructor for
  * plugins because func_get_args ( void ) returns a copy of all passed arguments
  * NOT references.  This causes problems with cross-referencing necessary for the
  * observer design pattern.
  */
  function plgContentTinyButStrong( &$subject ) {
    parent::__construct( $subject );
  }

	function onPrepareContent( &$article, &$params, $limitstart ) {

		// Search for plugin tags inside the article. There is a loop because the article can support several mergings.
		$tag_beg = '{tbs}';
		$tag_end = '{/tbs}';
		$p1 = strpos($article->text, $tag_beg);
		if ($p1===false) return ''; // If no TBS tag, then the article doesn't need the plugin.

		include_once('tinybutstrong_comm.php');
		include_once('tinybutstrong_spec.php');

		$TBS = false;
		
		$ArtId = $article->id;
		if (!tbs_plugin_CheckArticle($ArtId)) return ''; // Exit the plug if the article is not allowed
		
		tbs_plugin_InitTBS($TBS, $article->text);
		tbs_plugin_SqlDbInit($TBS); // retrieve SQL information and them them as custom property of $TBS

		// Wrappers for developpers
		$TBS->JoomlaParams &= $params;
		$TBS->JoomlaLimitstart &= $limitstart;
		$TBS->JoomlaArticle &= $article;
		
		$tag_lst = tbs_plugin_Loop($TBS, $p1, $tag_beg, $tag_end); // Search for all TBS tags and process them.
		
		$article->text = tbs_plugin_EndTBS($TBS); // ends the merge.
		
		unset($TBS);
		return '';
	}
	
}
