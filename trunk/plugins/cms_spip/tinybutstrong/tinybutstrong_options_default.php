<?php

/* 
Version 2009-10-08
The file 'tinybutstrong_options_default.php' is just a template for the options of this plugin.
Copy it as 'tinybutstrong_options.php' in order to define your own optins that will be kept
when this plugin is updated.

This is the options file for the SPIP plugin 'Dynamic Artciles with TBS'.
This file is not mentioned as an option file in "plugin.xml" because
it would be loaded for each page if it was so.
*/

/* ---------------
Identification of the <div> tag that contains the article.
Default value is : array('texte"','texte entry-content').
Enter a key string or an array of key strings which can identify the <div> tag that contains the article.
It can be the entire <div> tag, on a strink key that is after its '<' character.
In most of SPIP 2 templates, the article tag is coded <div class="texte entry-content">, and sometimes it
can have more classes ids. In SPIP 1.9.2 it is <div class="texte">.
Note: The plugin does need to found the part of the page that reprensents only the article contents.
This is because the plugin has to replace back '&nbsp;;' with ';', and also because it must not
merge the TBS tags of the article summum that could remain in the "content" header of the page. 
----------------- */
$GLOBALS['tbs_options']['ArtDiv'] = array('texte entry-content','texte"');

/* ---------------
Beginning of TBS tags
Default value is '['.
----------------- */
$GLOBALS['tbs_options']['ChrBeg'] = '[';

/* ---------------
End of TBS tags
Default value is ']'.
----------------- */
$GLOBALS['tbs_options']['ChrEnd'] = ']';

/* ---------------
Allowed prefix for PHP global variables
Default value is empty ('') which means all variables.
You can limit the usage of PHP global variables in articles by setting a prefix for allowed variables.
----------------- */
$GLOBALS['tbs_options']['VarPrefix'] = '';

/* ---------------
Allowed articles
Default value is empty ('') which mean none.
Enter ids of articles which are allowed to use plugin tags, separated with comas (,).
An error is prompted if a non-allowed article contains a plugin tag such as [tbs].
Set this parameter to limit the articles that SPIP authors can use plugin tags with.
You can use '*' to allow all articles, but use this value carefully because if option Direct MergeBlock
is also allowed then any SPIP author will be able to display the SPIP database.
----------------- */
$GLOBALS['tbs_options']['AllowedIds'] = '';

/* ---------------
Direct MergeBlock
Allow tags such as [tbs]mergeblock=...,sql=...,db=...[/tbs].
Values:
'no'     : Forbidden
'select' : Only SELECT queries (default value)
'all'    : All querie types and stored procedures
----------------- */
$GLOBALS['tbs_options']['MergeBlock'] = 'select';

/* ---------------
External scripts
Allow tags such as [tbs]script=...[/tbs].
Values:
'no'  : Forbidden
'loc' : Only from the Script location (default)
'all' : All external scripts
----------------- */
$GLOBALS['tbs_options']['Script'] = 'loc';

/* ---------------
Script location
Default value is ''This is the path (relative or absolute) where scripts specified in [tbs] tags can be stored.
----------------- */
$GLOBALS['tbs_options']['ScriptPath'] = '';

/* ---------------
Embedded scripts
Allow tags such as [tbs]embedded[/tbs].
Please note that those tags allow to run PHP scripts which is embedded inside an article
Values:
'no'  : Forbidden (default value)
'all' : Allowed
----------------- */
$GLOBALS['tbs_options']['Embedded'] = 'no';

/* ---------------
Full rights articles
Default value is ''.
Ids of articles which are allowed to use all [tbs] tags with all permissions.
You can seperate Ids with a coma (,). You can use '*' to allow all articles.
You can use this parameter to let Admin users to use [tbs] tags for thier own articles.
----------------- */
$GLOBALS['tbs_options']['FullRightsIds'] = '';




?>