<?php

// usefull for examples that contains links to this Example Viewer
$viewer = $_SERVER['SCRIPT_NAME'];

// parameters of this application
if (!isset($app_folder)) $app_folder = ''; // folder where to find the Example scripts
if (!isset($app_html)) $app_html = false; // true if the contents must not been dipslay directly, but saved into $app_html for a parent script
$app_echo = ($app_html===false);

// sub-template
$s = (isset($_GET['s'])) ? $_GET['s'] : '';

// example do be displayed
$e = (isset($_GET['e'])) ? $_GET['e'] : '';
$e_script   = $app_folder.'tbs_us_examples_'.$e.'.php';
$e_template = $app_folder.'tbs_us_examples_'.$e.(($s=='') ? '' : '_'.$s).'.htm';

if (!file_exists($e_template)) $e_template = $app_folder.'tbs_us_examples_'.$e.(($s=='') ? '' : '_'.$s).'.txt'; // case of text template

if ( ($e==='') || (!file_exists($e_script)) ) {
	$e = '_welcome';
	$e_script   = $app_folder.'tbs_us_examples__welcome.php';
	$e_template = $app_folder.'tbs_us_examples__welcome.htm';
}

// prepare data for retreiving the result of the merge
$sidebar = $app_folder.'tbs_us_examples__sidebar.htm'; // used by function f_sidebar_getmerged() and plug-in clsMyPluginRenderNothing
$sidebar_landmark = '<div id="main-body">';

// mode
$m = (isset($_GET['m'])) ? $_GET['m'] : '';
if ($m==='php') {
	// source of the PHP script
	$app_html = f_color_file($e_script, false, true);
	$app_html = f_source_create_html($app_html);
} elseif ($m==='source') {
	// source of the template
	$app_html = f_color_file($e_template, true, true);
	$app_html = f_source_create_html($app_html);
} elseif ($m==='template') {
	// display the template with the sidebar
	$app_html = file_get_contents($e_template);
} else {
	// display the result of the example with the sidebar
	$m = 'result';
	$other_prms = '&e='.$e.'&m='.$m; // can be needed by examples which have a GET form or autorefering links.
	$GLOBALS['_TBS_AutoInstallPlugIns'][] = 'clsMyPluginRenderNothing'; // Set the plug-in to be auto-loaded when TBS is instanciated.
	include($e_script);
	$app_html = $TBS->Source;
}

if ($m!=='result') {
	$sidebar_html = f_sidebar_getmerged();
	$p = strpos($app_html, $sidebar_landmark);
	if ($p===false) {
		// can happen with sub-templates
		// inserte the main title if missing
		if (strpos($app_html, '<h1')===false) {
			if ($s==='') {
				$title = "Example '".e."'";
			} else {
				$title = "Sub-template '".$s."' of example '".$e."'";
			}
			$title = '<h1>'.$title.'</h1>';
		} else {
			$title = '';
		}
		$app_html = str_replace('<body>', '<body>'.$title.$sidebar_landmark, $app_html);
		$app_html = str_replace('</body>', '</div></body>', $app_html);
	}
	$app_html = str_replace($sidebar_landmark, $sidebar_landmark.$sidebar_html, $app_html);
}

// Display the result.
if ($app_echo) {
	echo $app_html;
	exit;
} else {
	// Nothing to do. Another script is supposed to get the result in the variable $html. Used in the TinyButStrong web site for example.
}

// fonction for this application

/* Merges the Side-bar template and return the contents
*/
function f_sidebar_getmerged() {
	global $e, $m, $s, $e_script, $e_template, $sidebar;
	include_once('tbs_class.php');
	$TBS = new clsTinyButStrong;
	$TBS->Source = '[sidebar;file;getbody=body]';
	$TBS->MergeField('sidebar', $sidebar);
	$TBS->Show(TBS_NOTHING);
	return $TBS->Source;
}

/* Create an HTML body for viewing the PHP or HTML colored source
*/
function f_source_create_html($contents) {
	$title = 'Source code of '.$contents['file'];
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>TinyButStrong - '.$title.'</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="./tbs_us_examples_styles.css" rel="stylesheet" type="text/css" />
<style type="text/css">
'.$contents['css'].'
</style>
</head>
<body>
<h1>'.$title.'</h1>
<div id="main-body"> 
  <div id="example">
  '.$contents['main'].'
  </div>
</div>
</body>
</html>';

}

/* Color the contents of a file. Can be HTML or PHP.
   Returns an array with 'main', 'css' and 'file'
 */
function f_color_file($file, $ishtml, $lines) {

	$x = highlight_file($file, true);
	
	if ($ishtml) {
		f_color_tag($x, 't', 'table,tr,td,th');
		f_color_tag($x, 's', 'script');
		f_color_tag($x, 'c', 'style');
		f_color_tag($x, 'n');
	}
	
	if ($lines) {
		// display line number
		$n = 1 + substr_count($x, '<br />');
		$n_txt = '';
		for ($i=1;$i<=$n;$i++) {
			if ($i>1) $n_txt .= "\r\n";
			$n_txt .= $i; 
		}
		$x = '
<div style="float:left; width:30px; text-align:right; color: #666; border-right: #666 1px solid; padding-right: 2px;">
 <pre class="z">
  '.$n_txt.'
 </pre>
</div>
<div class="z" style="float:left; overflow:visible; width:520px; top:0; left:40px;">
 '.$x.'
 </div>';
		
	}

	$style = '
code {font-family: "Courier New", Courier, monospace; font-size: 12px; white-space:nowrap}
.z {font-family: "Courier New", Courier, monospace; font-size: 12px; margin:0; padding:0;} '."\r\n";
	if ($lines)  $style .= '.n {color: #009;} .t {color: #099;} .v {color: #00F;} .s {color: #900;} .c {color: #909;} '."\r\n";

	return array('main'=>$x, 'css'=>$style, 'file'=>basename($file));
}

/* Color a list of tags or all remaing tags with using a CSS class.
 * $txt must be a source code wich is a result of highlight_file().
 */
function f_color_tag(&$txt, $class, $tag='') {

	$z2 = '<span class="'.$class.'">';
	$zo = '&lt;';
	$zc = '&gt;';
	$zc_len = strlen('&gt;');
	
	$all = ($tag===''); // color all remaing tags

	if ($all) $txt = str_replace($zc,$zc.'</span>',$txt);

	if (is_string($tag)) $tag = explode(',', $tag);
	foreach ($tag as $t) {
		$p = 0;
		$z = $zo.$t;
		$z_len = strlen($z);
		do {
			$p = strpos($txt, $z, $p);
			if ($p!==false) {
				if ($all or (substr($txt,$p+$z_len,1)==='&')) { // the next char must be a ' ' or a '>'. In both case, it is converted by highlight_file() with a special char begining with '&'.
					if (($p>0) and (substr($txt,$p-2,2)!=='">')) { // the tag must not be previsouly colored 
						$p2 = strpos($txt, $zc, $p+$z_len);
						if ($p2!==false) {
							$x = substr($txt, $p, $p2 + $zc_len - $p);
							$x = str_replace('="','=<span class="v">"',$x); // color the value of attributes
							$x = str_replace('=\'','=<span class="v">\'',$x); // color the value of attributes
							$x = str_replace('"&','"</span>&',$x);
							$x = $z2.$x.'</span>';
							$txt = substr($txt,0,$p).$x.substr($txt,$p2 + $zc_len);
							$p = $p + strlen($x);
						} else {
							$p = false;
						}
					} else {
						$p = $p + $z_len;
					}
				} else {
					$p = $p + $z_len;
				}
			}
		} while ($p!==false);
		$z = $zo.'/'.$t.$zc;
		$txt = str_replace($z,$z2.$z.'</span>',$txt);
	}
	
}

/* Auto-loaded Plug-in for inserting the side-bar in running examples.
 */
class clsMyPluginRenderNothing {
	function OnInstall() {
		return array('BeforeShow');
	}
	function BeforeShow(&$Render) {
		$TBS = &$this->TBS;
		if ($TBS->_Mode==0) { // the engine is not in subtemplate-mode
			// insert the sidebar, it contains [onshow] fields
			global $sidebar, $sidebar_landmark;
			$sidebar_field = '[onshow;file='.$sidebar.';getbody=body]';
			if (strpos($TBS->Source,'<div id="sidebar"')===false) $TBS->Source = str_replace($sidebar_landmark, $sidebar_landmark.$sidebar_field, $this->TBS->Source); // The sidebar may be already existing in case of the cache example.
			$Render = TBS_NOTHING;
		}
	}
}