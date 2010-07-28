<?php

f_EchoLine("PHP Benches: Reverse seach in a String");
f_EchoLine("PHP version: ".PHP_VERSION);
f_EchoLine("OS type: ".PHP_OS." (".php_uname('s').")");

// speed tests

f_EchoLine();

/* ---------------------------------
*/

// initilize data and check the result of functions
$txt = f_GetHtml();
$zz = '<div';
$p_beg = strpos($txt, 'super_conforming_strrpos');
$res_php_stop = f_strrpos_php_stop($txt, $zz, $p_beg);
$res_php_stopcut = f_strrpos_php_stopcut($txt, $zz, $p_beg);
$res_manual_stop = f_strrpos_manual_stop($txt, $zz, $p_beg);
$res_direct_stop = f_strrpos_direct_stop($txt, $zz, $p_beg);
$res_direct_stop2 = f_strrpos_direct_stop2($txt, $zz, $p_beg);

f_EchoLine("len(txt) = ".strlen($txt)); 
f_EchoLine("p_beg = ".$p_beg." , extract: ".f_GiveExample($txt,$p_beg)); 
f_EchoLine("res_php_stop = ".$res_php_stop." , extract: ".f_GiveExample($txt,$res_php_stop)); 
f_EchoLine("res_php_stopcut = ".$res_php_stopcut." , extract: ".f_GiveExample($txt,$res_php_stopcut)); 
f_EchoLine("res_manual_stop = ".$res_manual_stop." , extract: ".f_GiveExample($txt,$res_manual_stop)); 
f_EchoLine("res_direct_stop = ".$res_direct_stop." , extract: ".f_GiveExample($txt,$res_direct_stop)); 
f_EchoLine("res_direct_stop2 = ".$res_direct_stop2." , extract: ".f_GiveExample($txt,$res_direct_stop2)); 


// run benches 

f_EchoLine();

$nbr = 1000;
$prm_1 =array(&$txt, $zz);
$prm_2 =array(&$txt, $zz, $p_beg);

$b0 = f_BechThisFct('f_Nothing');

$b_strrpos_php = f_BechThisFct('f_strrpos_php', $prm_1, $nbr) ;
$b_strrpos_manual = f_BechThisFct('f_strrpos_manual', $prm_1, $nbr);
f_Compare("php", $b_strrpos_php, "manual", $b_strrpos_manual);

$b_strrpos_direct_stop = f_BechThisFct('f_strrpos_direct_stop', $prm_2, $nbr);
$b_strrpos_direct_stop2 = f_BechThisFct('f_strrpos_direct_stop2', $prm_2, $nbr);
$b_strrpos_php_stop = f_BechThisFct('f_strrpos_php_stop', $prm_2, $nbr);
$b_strrpos_php_stopcut = f_BechThisFct('f_strrpos_php_stopcut', $prm_2, $nbr);
$b_strrpos_manual_stop = f_BechThisFct('f_strrpos_manual_stop', $prm_2, $nbr);

f_Compare("php_stop", $b_strrpos_php_stop, "php_stopcut", $b_strrpos_php_stopcut);
f_Compare("php_stop", $b_strrpos_php_stop, "manual_stop", $b_strrpos_manual_stop);
f_Compare("php_stopcut", $b_strrpos_php_stopcut, "direct_stop", $b_strrpos_direct_stop);
f_Compare("direct_stop", $b_strrpos_direct_stop, "direct_stop2", $b_strrpos_direct_stop2);



exit;

/* ---------------------------------
*/

function f_strrpos_php($txt, $x) {
	$p = strrpos($txt, $x);
	return $p;
}

function f_strrpos_php_stop($txt, $x, $stop) {
	$p = strrpos($txt, $x, $stop-strlen($txt));
	return $p;
}

function f_strrpos_php_stopcut($txt, $x, $stop) {
	$p = strrpos(substr($txt,0,$stop+1), $x);
	return $p;
}

function f_strrpos_manual($txt, $x) {
	$b = -1;
	while (($p=strpos($txt, $x,$b+1))!==false) {
		$b = $p; // continue to search
	}
	if ($b<0) {
		return false;
	} else {
		return $b;
	}
}

function f_strrpos_manual_stop($txt, $x, $stop) {
	$p = $stop;
	$b = -1;
	while ( (($p=strpos($txt, $x,$b+1))!==false) && ($p<$stop) ) {
		$b = $p; // continue to search
	}
	if ($b<0) {
		return false;
	} elseif ($p===$stop) {
		return $stop;
	} else {
		return $b;
	}
}

function f_strrpos_direct_stop($txt, $x, $stop) {
	$x0 = $x[0];
	$x_len = strlen($x);
	$p = $stop;
	$ok = false;
	$cont = true;
	do {
		if (($txt[$p]===$x0) && (substr($txt,$p,$x_len)===$x)) {
			$ok = true;
			$cont = false;
		} elseif ($p===0) {
			$cont = false;
		} else {
			$p--;
		}
	} while ($cont);
	if ($ok) {
		return $p;
	} else {
		return false;
	}
}

function f_strrpos_direct_stop2($txt, $x, $stop) {
	$x0 = $x[0];
	$x_len = strlen($x);
	$p = $stop;
	$ok = false;
	$cont = true;
	do {
		if ((substr($txt,$p,1)===$x0) && (substr($txt,$p,$x_len)===$x)) {
			$ok = true;
			$cont = false;
		} elseif ($p===0) {
			$cont = false;
		} else {
			$p--;
		}
	} while ($cont);
	if ($ok) {
		return $p;
	} else {
		return false;
	}
}

function f_GiveExample($txt,$p,$len=50) {
	return substr($txt, $p, $len);
}



/* ---------------------------------
*/

function f_Nothing() {
	$x = false;
	return $x;
}

function f_BechThisFct($fct, $prm=false, $nbr = 10000) {
	$x = false;
	if ($prm===false) $prm = array();
	$t1 = f_Timer();
	for ($i=0;$i<$nbr;$i++) {
		$x = call_user_func_array($fct, $prm);
	}
	$t2 = f_Timer();
	return ($t2-$t1);
}

function f_Timer() {
// compatible with PHP 4 and higher
	$x = microtime() ;
	$p = strpos($x,' ') ;
	if ($p===False) {
		$x = '0.0' ;
	} else {
		$x = substr($x,$p+1).substr($x,1,$p) ;
	} ;
	return (float)$x ;
}

function f_EchoLine($x='') {
	echo htmlentities($x)."<br />\r";
}

function f_Rate($a, $b) {
	return number_format($a/$b,2);
}

function f_Compare($a_name, $a_val, $b_name, $b_val) {
	if ($a_val>$b_val) {
		$x_val = $a_val;
		$a_val = $b_val;
		$b_val = $x_val;
		$x_name = $a_name;
		$a_name = $b_name;
		$b_name = $x_name;
	} 
	f_EchoLine( '['.$a_name.'] is '.number_format($b_val/$a_val,2).' time faster than ['.$b_name.'] , that is a gain of -'.number_format(100*($b_val-$a_val)/$b_val,2).'% compared to ['.$b_name.'].' );
}

function f_GetHtml() {
	
$x = <<<MY_HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head profile="http://purl.org/NET/erdf/profile">
 <title>PHP: strrpos - Manual</title>
 <style type="text/css" media="all">
  @import url("/styles/site.css");
  @import url("/styles/mirror.css");
  
 </style>
 <!--[if IE]><![if gte IE 6]><![endif]-->
  <style type="text/css" media="print">
   @import url("/styles/print.css");
  </style>
 <!--[if IE]><![endif]><![endif]-->
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

 <link rel="shortcut icon" href="/favicon.ico" />
 <link rel="contents" href="index.php" />
 <link rel="index" href="ref.strings.php" />
 <link rel="prev" href="function.strripos.php" />
 <link rel="next" href="function.strspn.php" />
 <link rel="schema.dc" href="http://purl.org/dc/elements/1.1/" />
 <link rel="schema.rdfs" href="http://www.w3.org/2000/01/rdf-schema#" />
 <link rev="canonical" rel="self alternate shorter shorturl shortlink" href="http://php.net/strrpos" />
 <link rel="license" href="http://creativecommons.org/licenses/by/3.0/" about="#content" />

 <link rel="canonical" href="http://php.net/manual/en/function.strrpos.php" />
 <script type="text/javascript" src="/userprefs.js"></script>
 <base href="http://fr.php.net/manual/en/function.strrpos.php" />
 <meta http-equiv="Content-language" value="en" />
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
<script type="text/javascript">
*(document).ready(function() {
	var toggleImage = function(elem) {
		if (*(elem).hasClass("shown")) {
			*(elem).removeClass("shown").addClass("hidden");
			*("img", elem).attr("src", "/images/notes-add.gif");
		}
		else {
			*(elem).removeClass("hidden").addClass("shown");
			*("img", elem).attr("src", "/images/notes-reject.gif");
		}
	};

	*(".refsect1 h3.title").each(function() {
        url = "http://bugs.php.net/report.php?bug_type=Documentation+problem&amp;manpage=" + *(this).parent().parent().attr("id") + "%23" + *(this).text();
		*(this).parent().prepend("<div class='reportbug'><a href='" + url + "'>Report a bug</a></div>");
		*(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' /></a> ");
	});
	*("#usernotes .head").each(function() {
		*(this).prepend("<a class='toggler shown' href='#'><img src='/images/notes-reject.gif' /></a> ");
	});
	*(".refsect1 h3.title .toggler").click(function() {
		*(this).parent().siblings().slideToggle("slow");
		toggleImage(this);
		return false;
	});
	*("#usernotes .head .toggler").click(function() {
		*(this).parent().next().slideToggle("slow");
		toggleImage(this);
		return false;
	});
});
</script>

</head>
<body>

<div id="headnav">
 <a href="/" rel="home"><img src="/images/php.gif"
 alt="PHP" width="120" height="67" id="phplogo" /></a>
 <div id="headmenu">
  <a href="/downloads.php">downloads</a> |
  <a href="/docs.php">documentation</a> |
  <a href="/FAQ.php">faq</a> |
  <a href="/support.php">getting help</a> |
  <a href="/mailing-lists.php">mailing lists</a> |
  <a href="/license">licenses</a> |
  <a href="http://wiki.php.net/">wiki</a> |
  <a href="http://bugs.php.net/">reporting bugs</a> |
  <a href="/sites.php">php.net sites</a> |
  <a href="/links.php">links</a> |
  <a href="/conferences/">conferences</a> |
  <a href="/my.php">my php.net</a>

 </div>
</div>

<div id="headsearch">
 <form method="post" action="/search.php" id="topsearch">
  <p>
   <span title="Keyboard shortcut: Alt+S (Win), Ctrl+S (Apple)">
    <span class="shortkey">s</span>earch for
   </span>
   <input type="text" name="pattern" value="" size="30" accesskey="s" />

   <span>in the</span>
   <select name="show">
    <option value="all"      >all php.net sites</option>
    <option value="local"    >this mirror only</option>
    <option value="quickref" selected="selected">function list</option>
    <option value="manual"   >online documentation</option>

    <option value="bugdb"    >bug database</option>
    <option value="news_archive">Site News Archive</option>
    <option value="changelogs">All Changelogs</option>
    <option value="pear"     >just pear.php.net</option>
    <option value="pecl"     >just pecl.php.net</option>
    <option value="talks"    >just talks.php.net</option>

    <option value="maillist" >general mailing list</option>
    <option value="devlist"  >developer mailing list</option>
    <option value="phpdoc"   >documentation mailing list</option>
   </select>
   <input type="image"
          src="/images/small_submit_white.gif"
          class="submit" alt="search" />
   <input type="hidden" name="lang" value="en" />
  </p>

 </form>
</div>

<div id="layout_2">
 <div id="leftbar">
<!--UdmComment-->
<ul class="toc">
 <li class="header home"><a href="index.php">PHP Manual</a></li>
 <li class="header up"><a href="funcref.php">Function Reference</a></li>
 <li class="header up"><a href="refs.basic.text.php">Text Processing</a></li>

 <li class="header up"><a href="book.strings.php">Strings</a></li>
 <li class="header up"><a href="ref.strings.php">String Functions</a></li>
 <li><a href="function.addcslashes.php">addcslashes</a></li>
 <li><a href="function.addslashes.php">addslashes</a></li>
 <li><a href="function.bin2hex.php">bin2hex</a></li>
 <li><a href="function.chop.php">chop</a></li>

 <li><a href="function.chr.php">chr</a></li>
 <li><a href="function.chunk-split.php">chunk_<span class="w"> </span>split</a></li>
 <li><a href="function.convert-cyr-string.php">convert_<span class="w"> </span>cyr_<span class="w"> </span>string</a></li>
 <li><a href="function.convert-uudecode.php">convert_<span class="w"> </span>uudecode</a></li>

 <li><a href="function.convert-uuencode.php">convert_<span class="w"> </span>uuencode</a></li>
 <li><a href="function.count-chars.php">count_<span class="w"> </span>chars</a></li>
 <li><a href="function.crc32.php">crc32</a></li>
 <li><a href="function.crypt.php">crypt</a></li>
 <li><a href="function.echo.php">echo</a></li>

 <li><a href="function.explode.php">explode</a></li>
 <li><a href="function.fprintf.php">fprintf</a></li>
 <li><a href="function.get-html-translation-table.php">get_<span class="w"> </span>html_<span class="w"> </span>translation_<span class="w"> </span>table</a></li>
 <li><a href="function.hebrev.php">hebrev</a></li>

 <li><a href="function.hebrevc.php">hebrevc</a></li>
 <li><a href="function.html-entity-decode.php">html_<span class="w"> </span>entity_<span class="w"> </span>decode</a></li>
 <li><a href="function.htmlentities.php">htmlentities</a></li>
 <li><a href="function.htmlspecialchars-decode.php">htmlspecialchars_<span class="w"> </span>decode</a></li>

 <li><a href="function.htmlspecialchars.php">htmlspecialchars</a></li>
 <li><a href="function.implode.php">implode</a></li>
 <li><a href="function.join.php">join</a></li>
 <li><a href="function.lcfirst.php">lcfirst</a></li>
 <li><a href="function.levenshtein.php">levenshtein</a></li>
 <li><a href="function.localeconv.php">localeconv</a></li>

 <li><a href="function.ltrim.php">ltrim</a></li>
 <li><a href="function.md5-file.php">md5_<span class="w"> </span>file</a></li>
 <li><a href="function.md5.php">md5</a></li>
 <li><a href="function.metaphone.php">metaphone</a></li>
 <li><a href="function.money-format.php">money_<span class="w"> </span>format</a></li>

 <li><a href="function.nl-langinfo.php">nl_<span class="w"> </span>langinfo</a></li>
 <li><a href="function.nl2br.php">nl2br</a></li>
 <li><a href="function.number-format.php">number_<span class="w"> </span>format</a></li>
 <li><a href="function.ord.php">ord</a></li>
 <li><a href="function.parse-str.php">parse_<span class="w"> </span>str</a></li>

 <li><a href="function.print.php">print</a></li>
 <li><a href="function.printf.php">printf</a></li>
 <li><a href="function.quoted-printable-decode.php">quoted_<span class="w"> </span>printable_<span class="w"> </span>decode</a></li>
 <li><a href="function.quoted-printable-encode.php">quoted_<span class="w"> </span>printable_<span class="w"> </span>encode</a></li>

 <li><a href="function.quotemeta.php">quotemeta</a></li>
 <li><a href="function.rtrim.php">rtrim</a></li>
 <li><a href="function.setlocale.php">setlocale</a></li>
 <li><a href="function.sha1-file.php">sha1_<span class="w"> </span>file</a></li>
 <li><a href="function.sha1.php">sha1</a></li>

 <li><a href="function.similar-text.php">similar_<span class="w"> </span>text</a></li>
 <li><a href="function.soundex.php">soundex</a></li>
 <li><a href="function.sprintf.php">sprintf</a></li>
 <li><a href="function.sscanf.php">sscanf</a></li>
 <li><a href="function.str-getcsv.php">str_<span class="w"> </span>getcsv</a></li>

 <li><a href="function.str-ireplace.php">str_<span class="w"> </span>ireplace</a></li>
 <li><a href="function.str-pad.php">str_<span class="w"> </span>pad</a></li>
 <li><a href="function.str-repeat.php">str_<span class="w"> </span>repeat</a></li>
 <li><a href="function.str-replace.php">str_<span class="w"> </span>replace</a></li>

 <li><a href="function.str-rot13.php">str_<span class="w"> </span>rot13</a></li>
 <li><a href="function.str-shuffle.php">str_<span class="w"> </span>shuffle</a></li>
 <li><a href="function.str-split.php">str_<span class="w"> </span>split</a></li>
 <li><a href="function.str-word-count.php">str_<span class="w"> </span>word_<span class="w"> </span>count</a></li>

 <li><a href="function.strcasecmp.php">strcasecmp</a></li>
 <li><a href="function.strchr.php">strchr</a></li>
 <li><a href="function.strcmp.php">strcmp</a></li>
 <li><a href="function.strcoll.php">strcoll</a></li>
 <li><a href="function.strcspn.php">strcspn</a></li>
 <li><a href="function.strip-tags.php">strip_<span class="w"> </span>tags</a></li>

 <li><a href="function.stripcslashes.php">stripcslashes</a></li>
 <li><a href="function.stripos.php">stripos</a></li>
 <li><a href="function.stripslashes.php">stripslashes</a></li>
 <li><a href="function.stristr.php">stristr</a></li>
 <li><a href="function.strlen.php">strlen</a></li>
 <li><a href="function.strnatcasecmp.php">strnatcasecmp</a></li>

 <li><a href="function.strnatcmp.php">strnatcmp</a></li>
 <li><a href="function.strncasecmp.php">strncasecmp</a></li>
 <li><a href="function.strncmp.php">strncmp</a></li>
 <li><a href="function.strpbrk.php">strpbrk</a></li>
 <li><a href="function.strpos.php">strpos</a></li>
 <li><a href="function.strrchr.php">strrchr</a></li>

 <li><a href="function.strrev.php">strrev</a></li>
 <li><a href="function.strripos.php">strripos</a></li>
 <li class="active"><a href="function.strrpos.php">strrpos</a></li>
 <li><a href="function.strspn.php">strspn</a></li>
 <li><a href="function.strstr.php">strstr</a></li>
 <li><a href="function.strtok.php">strtok</a></li>

 <li><a href="function.strtolower.php">strtolower</a></li>
 <li><a href="function.strtoupper.php">strtoupper</a></li>
 <li><a href="function.strtr.php">strtr</a></li>
 <li><a href="function.substr-compare.php">substr_<span class="w"> </span>compare</a></li>
 <li><a href="function.substr-count.php">substr_<span class="w"> </span>count</a></li>

 <li><a href="function.substr-replace.php">substr_<span class="w"> </span>replace</a></li>
 <li><a href="function.substr.php">substr</a></li>
 <li><a href="function.trim.php">trim</a></li>
 <li><a href="function.ucfirst.php">ucfirst</a></li>
 <li><a href="function.ucwords.php">ucwords</a></li>

 <li><a href="function.vfprintf.php">vfprintf</a></li>
 <li><a href="function.vprintf.php">vprintf</a></li>
 <li><a href="function.vsprintf.php">vsprintf</a></li>
 <li><a href="function.wordwrap.php">wordwrap</a></li>
</ul><!--/UdmComment-->

 </div>
 <div id="content" class="manual/en">

<!--UdmComment-->
<div class="manualnavbar manualnavbar_top">
 <span class="next">
  <a href="function.strspn.php">strspn<img src="/images/caret-r.gif" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">
  <a href="function.strripos.php"><img src="/images/caret-l.gif" alt="&lt;" width="11" height="7" />strripos</a>
 </span>
 <hr />

 <span class="lastupdated">Last updated: Fri, 23 Jul 2010</span>
 <div class="langchooser">
  <form action="/manual/change.php" method="get">
   <p>view this page in </p><fieldset><select name="page">
    <option value="pt_BR/function.strrpos.php">Brazilian Portuguese</option>
    <option value="fr/function.strrpos.php">French</option>
    <option value="de/function.strrpos.php">German</option>

    <option value="ja/function.strrpos.php">Japanese</option>
    <option value="pl/function.strrpos.php">Polish</option>
    <option value="ro/function.strrpos.php">Romanian</option>
    <option value="fa/function.strrpos.php">Persian</option>
    <option value="es/function.strrpos.php">Spanish</option>
    <option value="tr/function.strrpos.php">Turkish</option>

    <option value="help-translate.php">Other</option>
   </select>
   <input type="image" src="/images/small_submit.gif" id="changeLangImage" alt="Change language" />
  </fieldset></form>
 </div>
</div>
<!--/UdmComment-->

<div id="function.strrpos" class="refentry">
 <div class="refnamediv">

  <h1 class="refname">strrpos</h1>
  <p class="verinfo">(PHP 4, PHP 5)</p><p class="refpurpose"><span class="refname">strrpos</span> &mdash; <span class="dc-title">Find position of last occurrence of a char in a string</span></p>

 </div>
 
 <div class="refsect1 description">
  <h3 class="title">Description</h3>

  <div class="methodsynopsis dc-description">
    <span class="type">int</span>  <span class="methodname"><b>strrpos</b></span>
    ( <span class="methodparam"> <span class="type">string</span>  <tt class="parameter">*haystack</tt></span>
   , <span class="methodparam"> <span class="type">string</span>  <tt class="parameter">*needle</tt></span>

   [, <span class="methodparam"> <span class="type">int</span>  <tt class="parameter">*offset</tt><span class="initializer"> = 0</span></span>
  ] )</div>

  <p class="para rdfs-comment">
   Returns the numeric position of the last occurrence of
   <i><tt class="parameter">needle</tt></i> in the
   <i><tt class="parameter">haystack</tt></i> string.  Note that the needle in
   this case can only be a single character in PHP 4.  If a string is passed
   as the needle, then only the first character of that string will
   be used.
  </p>

 </div>


 <div class="refsect1 parameters">
  <h3 class="title">Parameters</h3>
  <p class="para">
   <dl>

    <dt>

     <span class="term"><i><tt class="parameter">haystack</tt></i></span>
     <dd>

      <p class="para">
       The string to search in.
      </p>
     </dd>

    </dt>

    <dt>

     <span class="term"><i><tt class="parameter">needle</tt></i></span>
     <dd>

      <p class="para">
       If <i><tt class="parameter">needle</tt></i> is not a string, it is converted
       to an integer and applied as the ordinal value of a character.
      </p>

     </dd>

    </dt>

    <dt>

     <span class="term"><i><tt class="parameter">offset</tt></i></span>
     <dd>

      <p class="para">

       May be specified to begin searching an arbitrary number of characters into
       the string.  Negative values will stop searching at an arbitrary point
       prior to the end of the string.
      </p>
     </dd>

    </dt>

   </dl>

  </p>
 </div>

 <div class="refsect1 returnvalues">
  <h3 class="title">Return Values</h3>
  <p class="para">
   Returns the position where the needle exists. Returns <b><tt>FALSE</tt></b> if the needle
   was not found.
  </p>
 </div>

 
 <div class="refsect1 changelog">
  <h3 class="title">Changelog</h3>
  <p class="para">
   <table class="doctable informaltable">
    
     <thead valign="middle">
      <tr valign="middle">
       <th>Version</th>
       <th>Description</th>

      </tr>

     </thead>

     <tbody valign="middle" class="tbody">
      <tr valign="middle">
       <td align="left">5.0.0</td>
       <td align="left">
        The <i><tt class="parameter">needle</tt></i> may now be a string of more than one
        character.
       </td>

      </tr>

      <tr valign="middle">
       <td align="left">5.0.0</td>
       <td align="left">
        The <i><tt class="parameter">offset</tt></i> parameter was introduced.
       </td>
      </tr>

     </tbody>
    
   </table>

  </p>
 </div>

 
 <div class="refsect1 examples">
  <h3 class="title">Examples</h3>
  <p class="para">

   <div class="example">
    <p><b>Example #1 Checking if a needle is in the haystack</b></p>
    <div class="example-contents"><p>
     It is easy to mistake the return values for &quot;character found at
     position 0&quot; and &quot;character not found&quot;.  Here&#039;s how to detect
     the difference:
    </p></div>
    <div class="example-contents">

<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br />*pos&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">strrpos</span><span style="color: #007700">(</span><span style="color: #0000BB">*mystring</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"b"</span><span style="color: #007700">);<br />if&nbsp;(</span><span style="color: #0000BB">*pos&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">)&nbsp;{&nbsp;</span><span style="color: #FF8000">//&nbsp;note:&nbsp;three&nbsp;equal&nbsp;signs<br />&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;not&nbsp;found...<br /></span><span style="color: #007700">}<br /><br /></span><span style="color: #0000BB">?&gt;</span>

</span>
</code></div>
    </div>

   </div>
  </p>
  <p class="para">
   <div class="example">
    <p><b>Example #2 Searching with offsets</b></p>
    <div class="example-contents">

<div class="phpcode"><code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br />*foo&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">"0123456789a123456789b123456789c"</span><span style="color: #007700">;<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">strrpos</span><span style="color: #007700">(</span><span style="color: #0000BB">*foo</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'7'</span><span style="color: #007700">,&nbsp;-</span><span style="color: #0000BB">5</span><span style="color: #007700">));&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Starts&nbsp;looking&nbsp;backwards&nbsp;five&nbsp;positions<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;from&nbsp;the&nbsp;end.&nbsp;Result:&nbsp;int(17)<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">strrpos</span><span style="color: #007700">(</span><span style="color: #0000BB">*foo</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'7'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">20</span><span style="color: #007700">));&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Starts&nbsp;searching&nbsp;20&nbsp;positions&nbsp;into&nbsp;the<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;//&nbsp;string.&nbsp;Result:&nbsp;int(27)<br /><br /></span><span style="color: #0000BB">var_dump</span><span style="color: #007700">(</span><span style="color: #0000BB">strrpos</span><span style="color: #007700">(</span><span style="color: #0000BB">*foo</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'7'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">28</span><span style="color: #007700">));&nbsp;&nbsp;</span><span style="color: #FF8000">//&nbsp;Result:&nbsp;bool(false)<br /></span><span style="color: #0000BB">?&gt;</span>

</span>
</code></div>
    </div>

   </div>
  </p>
 </div>


 <div class="refsect1 seealso">
  <h3 class="title">See Also</h3>

  <p class="para">
   <ul class="simplelist">
    <li class="member"><span class="function"><a href="function.strpos.php" class="function" rel="rdfs-seeAlso">strpos()</a> - Find position of first occurrence of a string</span></li>
    <li class="member"><span class="function"><a href="function.strripos.php" class="function" rel="rdfs-seeAlso">strripos()</a> - Find position of last occurrence of a case-insensitive string in a string</span></li>
    <li class="member"><span class="function"><a href="function.strrchr.php" class="function" rel="rdfs-seeAlso">strrchr()</a> - Find the last occurrence of a character in a string</span></li>

    <li class="member"><span class="function"><a href="function.substr.php" class="function" rel="rdfs-seeAlso">substr()</a> - Return part of a string</span></li>
    <li class="member"><span class="function"><a href="function.stristr.php" class="function" rel="rdfs-seeAlso">stristr()</a> - Case-insensitive strstr</span></li>
    <li class="member"><span class="function"><a href="function.strstr.php" class="function" rel="rdfs-seeAlso">strstr()</a> - Find first occurrence of a string</span></li>
   </ul>

  </p>
 </div>


</div><br /><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="function.strspn.php">strspn<img src="/images/caret-r.gif" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">

  <a href="function.strripos.php"><img src="/images/caret-l.gif" alt="&lt;" width="11" height="7" />strripos</a>
 </span>
 <hr />
 <span class="lastupdated">Last updated: Fri, 23 Jul 2010</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>

<!--/UdmComment-->


<div id="usernotes">
 <div class="head">
  <span class="action"><a href="/manual/add-note.php?sect=function.strrpos&amp;redirect=http://fr.php.net/manual/en/function.strrpos.php"><img src="/images/notes-add.gif" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.strrpos&amp;redirect=http://fr.php.net/manual/en/function.strrpos.php">add a note</a></small></span>
  <small>User Contributed Notes</small><br />
  <strong>strrpos</strong>
 </div><div id="allnotes">

 <a name="97126"></a>
 <div class="note">
  <strong>andre at admolin dot com</strong><br />
  <a href="#97126">02-Apr-2010 04:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
An usage of strrpos() is to find the last space of a string. With this, we can cut an string without cutting any word.<br />
<br />
<span class="default">&lt;?php<br />

</span><span class="keyword">function </span><span class="default">short_str</span><span class="keyword">( </span><span class="default">*str</span><span class="keyword">, </span><span class="default">*len</span><span class="keyword">, </span><span class="default">*cut </span><span class="keyword">= </span><span class="default">true </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; if ( </span><span class="default">strlen</span><span class="keyword">( </span><span class="default">*str </span><span class="keyword">) &lt;= </span><span class="default">*len </span><span class="keyword">) return </span><span class="default">*str</span><span class="keyword">;<br />

&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; return ( </span><span class="default">*cut </span><span class="keyword">? </span><span class="default">substr</span><span class="keyword">( </span><span class="default">*str</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">*len </span><span class="keyword">) : </span><span class="default">substr</span><span class="keyword">( </span><span class="default">*str</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">strrpos</span><span class="keyword">( </span><span class="default">substr</span><span class="keyword">( </span><span class="default">*str</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">*len </span><span class="keyword">), </span><span class="string">' ' </span><span class="keyword">) ) ) . </span><span class="string">'...'</span><span class="keyword">;<br />

}<br />
</span><span class="default">?&gt;<br />
</span><br />
Using the function:<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="comment"># This is a test st...<br />
</span><span class="keyword">echo </span><span class="default">short_str</span><span class="keyword">( </span><span class="string">'This is a test string.'</span><span class="keyword">, </span><span class="default">17 </span><span class="keyword">);<br />

</span><span class="comment"># This is a test...<br />
</span><span class="keyword">echo </span><span class="default">short_str</span><span class="keyword">( </span><span class="string">'This is a test string.'</span><span class="keyword">, </span><span class="default">17</span><span class="keyword">, </span><span class="default">false </span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>

 </div>
 <a name="93829"></a>
 <div class="note">
  <strong>jriddy at obfuscation dot isnt dot spam-safe dot com</strong><br />
  <a href="#93829">01-Oct-2009 10:24</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you're trying to do what "Alexandre" did a few posts below this one, that is, removing trailing slashes, just use rtrim() for simple stuff, and a preg function for more complicated removals.<br />

<br />
Either one of the following functions will remove a trailing slash unless it's a root folder.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">trimTrailingSlashes1</span><span class="keyword">( </span><span class="default">*str </span><span class="keyword">) {<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// It's always a good idea to do this just to make sure.<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*str </span><span class="keyword">= </span><span class="default">trim</span><span class="keyword">(</span><span class="default">*str</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// If it's root, don't rtrim() the only slash away.<br />
&nbsp;&nbsp;&nbsp; // Passing a character mask to rtrim makes it look for the<br />
&nbsp;&nbsp;&nbsp; // characters you specify.<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">*str </span><span class="keyword">== </span><span class="string">'/' </span><span class="keyword">? </span><span class="default">*str </span><span class="keyword">: </span><span class="default">rtrim</span><span class="keyword">(</span><span class="default">*str</span><span class="keyword">, </span><span class="string">'/'</span><span class="keyword">);<br />

}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="92158"></a>
 <div class="note">
  <strong>maxmike at gmail dot com</strong><br />

  <a href="#92158">12-Jul-2009 07:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I've got a simple method of performing a reverse strpos which may be of use.&nbsp; This version I have treats the offset very simply:<br />
Positive offsets search backwards from the supplied string index.<br />
Negative offsets search backwards from the position of the character that many characters from the end of the string.<br />
<br />
Here is an example of backwards stepping through instances of a string with this function:<br />

<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">backwardStrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*length </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*offset </span><span class="keyword">= (</span><span class="default">*offset </span><span class="keyword">&gt; </span><span class="default">0</span><span class="keyword">)?(</span><span class="default">*length </span><span class="keyword">- </span><span class="default">*offset</span><span class="keyword">):</span><span class="default">abs</span><span class="keyword">(</span><span class="default">*offset</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">), </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">), </span><span class="default">*offset</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; return (</span><span class="default">*pos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">)?</span><span class="default">false</span><span class="keyword">:( </span><span class="default">*length </span><span class="keyword">- </span><span class="default">*pos </span><span class="keyword">- </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) );<br />

}<br />
<br />
</span><span class="default">*pos </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
</span><span class="default">*count </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />
echo </span><span class="string">"Test1&lt;br/&gt;"</span><span class="keyword">;<br />

while((</span><span class="default">*pos </span><span class="keyword">= </span><span class="default">backwardStrpos</span><span class="keyword">(</span><span class="string">"012340567890"</span><span class="keyword">, </span><span class="string">"0"</span><span class="keyword">, </span><span class="default">*pos</span><span class="keyword">)) !== </span><span class="default">false</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; echo </span><span class="default">*pos</span><span class="keyword">.</span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*pos</span><span class="keyword">--;<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">*pos </span><span class="keyword">&lt; </span><span class="default">0</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo </span><span class="string">"Done&lt;br/&gt;"</span><span class="keyword">;break;<br />

&nbsp;&nbsp;&nbsp; }<br />
}<br />
echo </span><span class="string">"---===---&lt;br/&gt;\nTest2&lt;br/&gt;"</span><span class="keyword">;<br />
echo </span><span class="default">backwardStrpos</span><span class="keyword">(</span><span class="string">"12341234"</span><span class="keyword">, </span><span class="string">"1"</span><span class="keyword">, </span><span class="default">2</span><span class="keyword">).</span><span class="string">"&lt;br/&gt;"</span><span class="keyword">;<br />

echo </span><span class="default">backwardStrpos</span><span class="keyword">(</span><span class="string">"12341234"</span><span class="keyword">, </span><span class="string">"1"</span><span class="keyword">, -</span><span class="default">2</span><span class="keyword">);<br />
</span><span class="default">?&gt;<br />
</span><br />
Outputs:<br />
Test1<br />

11<br />
5<br />
0<br />
Done<br />
---===---<br />
Test2<br />
0<br />
4<br />
<br />

With Test2 the first line checks from the first 3 in "12341234" and runs backwards until it finds a 1 (at position 0)<br />
<br />
The second line checks from the second 2 in "12341234" and seeks towards the beginning for the first 1 it finds (at position 4).<br />
<br />
This function is useful for php4 and also useful if the offset parameter in the existing strrpos is equally confusing to you as it is for me.</span>
</code></div>
  </div>
 </div>
 <a name="87769"></a>
 <div class="note">

  <strong>alexandre at NOSPAM dot pixeline dot be</strong><br />
  <a href="#87769">20-Dec-2008 05:35</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
I needed to check if a variable that contains a generated folder name based on user input had a trailing slash.
<br />

<br />
This did the trick:
<br />

<br />

<span class="default">&lt;?php
<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">// Detect and remove a trailing slash
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*root_folder </span><span class="keyword">= ((</span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*root_folder</span><span class="keyword">, </span><span class="string">'/'</span><span class="keyword">) + </span><span class="default">1</span><span class="keyword">) == </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*root_folder</span><span class="keyword">)) ? </span><span class="default">substr</span><span class="keyword">(</span><span class="default">*root_folder</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, - </span><span class="default">1</span><span class="keyword">) : </span><span class="default">*root_folder</span><span class="keyword">;

<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="84198"></a>
 <div class="note">
  <strong>eagleeye at eeisi dot com</strong><br />
  <a href="#84198">03-Jul-2008 02:44</a>

  <div class="text">
<div class="phpcode"><code><span class="html">
I had a problem where I was using the following in my .htaccess file.<br />
<br />
php_value auto_prepend_file "pre.php"<br />
php_value auto_append_file "post.php"<br />
<br />
Not knowing how to prevent the htaccess directives from cascading, without having to put an override .htaccess in each subfolder, I figured, just prevent output in my pre and post scripts if we weren't in the root folder!<br />
<br />
I did it using this line of code:<br />
<br />


<br />
Prevents execution of the rest of the script, and most importantly, doesn't output anything before any other headers may be sent by things in other folders (like my wiki site).</span>
</code></div>
  </div>
 </div>
 <a name="83901"></a>
 <div class="note">
  <strong>dubayou [ot] g.com</strong><br />

  <a href="#83901">17-Jun-2008 07:37</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
not all code is the same.<br />
it should be coded such as<br />
<span class="default">&lt;?php<br />
*a </span><span class="keyword">= </span><span class="string">"abcd"</span><span class="keyword">;<br />

if(</span><span class="default">is_bool</span><span class="keyword">(</span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*a</span><span class="keyword">,</span><span class="string">"a"</span><span class="keyword">)))<br />
echo </span><span class="string">"Bad"</span><span class="keyword">;<br />
else<br />
echo </span><span class="string">"Good"</span><span class="keyword">;<br />

</span><span class="default">Gives</span><span class="keyword">:<br />
</span><span class="default">Good<br />
<br />
This is how you might expect it to work</span><span class="keyword">, </span><span class="default">but it will fail</span><span class="keyword">;<br />
&lt;?<br />
</span><span class="default">*a </span><span class="keyword">= </span><span class="string">"abcd"</span><span class="keyword">;<br />

if(!</span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*a</span><span class="keyword">,</span><span class="string">"a"</span><span class="keyword">))<br />
echo </span><span class="string">"Bad"</span><span class="keyword">;<br />
else<br />
echo </span><span class="string">"Good"</span><span class="keyword">;<br />

<br />
</span><span class="default">Gives</span><span class="keyword">:<br />
</span><span class="default">Bad</span>
</span>
</code></div>
  </div>
 </div>
 <a name="80008"></a>
 <div class="note">
  <strong>dixonmd at gmail dot com</strong><br />

  <a href="#80008">24-Dec-2007 11:42</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
&lt;php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *pos = strlen(string *haystack) - strpos (strrev(string *haystack), strrev(string *needle)) - strlen(string *needle);<br />
?&gt;<br />
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; If in the needle there is more than one character then in php 4 we can use the above statement for finding the position of last occurrence of a substring in a string instead of strrpos. Because in php 4 strrpos uses the first character of the substring.<br />

<br />
eg : <br />
&lt;php<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *haystack = "you you you you you";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *needle = "you";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *pos1 = strlen(*haystack) - strpos (strrev(*haystack), strrev(*needle)) - strlen(*needle);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo *pos1 . "&lt;br&gt;";<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *pos2 strrpos(*haystack, *needle);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; echo *pos2 . "&lt;br&gt;";<br />

?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="78556"></a>
 <div class="note">
  <strong>t dot hornberger at yatego dot com</strong><br />
  <a href="#78556">17-Oct-2007 01:50</a>

  <div class="text">
<div class="phpcode"><code><span class="html">
the function posted is false, hier the correction:<br />
<br />
function rstrpos (*haystack, *needle, *offset)<br />
{<br />
&nbsp;&nbsp;&nbsp; *size = strlen (*haystack);<br />
&nbsp;&nbsp;&nbsp; *pos = strpos (strrev(*haystack), strrev(*needle), *size - *offset);<br />
&nbsp;&nbsp; <br />

&nbsp;&nbsp;&nbsp; if (*pos === false)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return false;<br />
&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; return *size - *pos - strlen(*needle);<br />
}</span>
</code></div>
  </div>

 </div>
 <a name="78499"></a>
 <div class="note">
  <strong>Daniel Brinca</strong><br />
  <a href="#78499">15-Oct-2007 02:41</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Here is a simple function to find the position of the next occurrence of needle in haystack, but searching backwards&nbsp; (lastIndexOf type function):<br />

<br />
//search backwards for needle in haystack, and return its position<br />
function rstrpos (*haystack, *needle, *offset){<br />
&nbsp;&nbsp;&nbsp; *size = strlen (*haystack);<br />
&nbsp;&nbsp;&nbsp; *pos = strpos (strrev(*haystack), *needle, *size - *offset);<br />
&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; if (*pos === false)<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return false;<br />

&nbsp;&nbsp;&nbsp; <br />
&nbsp;&nbsp;&nbsp; return *size - *pos;<br />
}<br />
<br />
Note: supports full strings as needle</span>
</code></div>
  </div>
 </div>
 <a name="78001"></a>

 <div class="note">
  <strong>pb at tdcspace dot dk</strong><br />
  <a href="#78001">23-Sep-2007 04:26</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
what the hell are you all doing. Wanna find the *next* last from a specific position because strrpos is useless with the "offset" option, then....<br />
<br />
ex: find 'Z' in *str from position *p,&nbsp; backward...<br />

<br />
while(*p &gt; -1 and *str{*p} &lt;&gt; 'Z') *p--;<br />
<br />
Anyone will notice *p = -1 means: *not found* and that you must ensure a valid start offset in *p, that is &gt;=0 and &lt; string length. Doh</span>
</code></div>
  </div>
 </div>

 <a name="76447"></a>
 <div class="note">
  <strong>brian at enchanter dot net</strong><br />
  <a href="#76447">16-Jul-2007 04:47</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
The documentation for 'offset' is misleading.<br />
<br />
It says, "offset may be specified to begin searching an arbitrary number of characters into the string. Negative values will stop searching at an arbitrary point prior to the end of the string."<br />

<br />
This is confusing if you think of strrpos as starting at the end of the string and working backwards.<br />
<br />
A better way to think of offset is:<br />
<br />
- If offset is positive, then strrpos only operates on the part of the string from offset to the end. This will usually have the same results as not specifying an offset, unless the only occurences of needle are before offset (in which case specifying the offset won't find the needle).<br />
<br />
- If offset is negative, then strrpos only operates on that many characters at the end of the string. If the needle is farther away from the end of the string, it won't be found.<br />
<br />
If, for example, you want to find the last space in a string before the 50th character, you'll need to do something like this:<br />
<br />
strrpos(*text, " ", -(strlen(*text) - 50));<br />

<br />
If instead you used strrpos(*text, " ", 50), then you would find the last space between the 50th character and the end of the string, which may not have been what you were intending.</span>
</code></div>
  </div>
 </div>
 <a name="74474"></a>
 <div class="note">
  <strong>jafet at g dot m dot a dot i dot l dot com</strong><br />
  <a href="#74474">13-Apr-2007 04:08</a>

  <div class="text">
<div class="phpcode" id="you_found_me"><code><span class="html">
It would probably be good if someone would care to merge these little thoughts together...<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">super_conforming_strrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">)<br />

{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Why does strpos() do this? Anyway...<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">)) </span><span class="default">*needle </span><span class="keyword">= </span><span class="default">ord</span><span class="keyword">(</span><span class="default">intval</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));<br />

&nbsp;&nbsp;&nbsp; if(!</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">)) </span><span class="default">*haystack </span><span class="keyword">= </span><span class="default">strval</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Setup<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">intval</span><span class="keyword">(</span><span class="default">*offset</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*hlen </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*nlen </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Intermezzo<br />

&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">*nlen </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="default">__FUNCTION__</span><span class="keyword">.</span><span class="string">'(): Empty delimiter.'</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">*offset </span><span class="keyword">&lt; </span><span class="default">0</span><span class="keyword">)<br />

&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*haystack </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, -</span><span class="default">*offset</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;<br />

&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; elseif(</span><span class="default">*offset </span><span class="keyword">&gt;= </span><span class="default">*hlen</span><span class="keyword">)<br />
&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="default">__FUNCTION__</span><span class="keyword">.</span><span class="string">'(): Offset not contained in string.'</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># More setup<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*hrev </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*nrev </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Search<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*hrev</span><span class="keyword">, </span><span class="default">*nrev</span><span class="keyword">, </span><span class="default">*offset</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; if(</span><span class="default">*pos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; else return </span><span class="default">*hlen </span><span class="keyword">- </span><span class="default">*nlen </span><span class="keyword">- </span><span class="default">*pos</span><span class="keyword">;<br />

}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="74454"></a>
 <div class="note">
  <strong>jafet at g dot m dot a dot i dot l dot com</strong><br />

  <a href="#74454">12-Apr-2007 10:57</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Full strpos() functionality, by yours truly.<br />
<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">conforming_strrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">)<br />

{<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Why does strpos() do this? Anyway...<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(!</span><span class="default">is_string</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">)) </span><span class="default">*needle </span><span class="keyword">= </span><span class="default">ord</span><span class="keyword">(</span><span class="default">intval</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*haystack </span><span class="keyword">= </span><span class="default">strval</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Parameters<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*hlen </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*nlen </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Come on, this is a feature too<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">*nlen </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">)<br />

&nbsp;&nbsp;&nbsp; {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">trigger_error</span><span class="keyword">(</span><span class="default">__FUNCTION__</span><span class="keyword">.</span><span class="string">'(): Empty delimiter.'</span><span class="keyword">, </span><span class="default">E_USER_WARNING</span><span class="keyword">);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />

&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">intval</span><span class="keyword">(</span><span class="default">*offset</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*hrev </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*nrev </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment"># Search<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*hrev</span><span class="keyword">, </span><span class="default">*nrev</span><span class="keyword">, </span><span class="default">*offset</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; if(</span><span class="default">*pos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; else return </span><span class="default">*hlen </span><span class="keyword">- </span><span class="default">*nlen </span><span class="keyword">- </span><span class="default">*pos</span><span class="keyword">;<br />

}<br />
</span><span class="default">?&gt;<br />
</span><br />
Note that *offset is evaluated from the end of the string.<br />
<br />
Also note that conforming_strrpos() performs some five times slower than strpos(). Just a thought.</span>
</code></div>
  </div>
 </div>
 <a name="73666"></a>

 <div class="note">
  <strong>mijsoot_at_gmail_dot_com</strong><br />
  <a href="#73666">06-Mar-2007 10:43</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To begin, i'm sorry for my English.<br />
So, I needed of one function which gives me the front last position of a character. <br />
Then I said myself that it should be better to make one which gives the "N" last position.<br />

<br />
*return_context = "1173120681_0__0_0_Mijsoot_Thierry";<br />
<br />
// Here i need to find = "Mijsoot_Thierry"<br />
<br />
//echo *return_context."&lt;br /&gt;";// -- DEBUG<br />
<br />
function findPos(*haystack,*needle,*position){<br />
&nbsp;&nbsp;&nbsp; *pos = strrpos(*haystack, *needle);<br />

&nbsp;&nbsp;&nbsp; if(*position&gt;1){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *position --;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *haystack = substr(*haystack, 0, *pos);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *pos = findPos(*haystack,*needle,*position);<br />

&nbsp;&nbsp;&nbsp; }else{<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; // echo *haystack."&lt;br /&gt;"; // -- DEBUG<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return *pos;<br />
&nbsp;&nbsp;&nbsp; }<br />

&nbsp;&nbsp;&nbsp; return *pos;<br />
}<br />
<br />
var_dump(findPos(*return_context,"_",2)); // -- TEST</span>
</code></div>
  </div>
 </div>
 <a name="72682"></a>
 <div class="note">

  <strong>Christ Off</strong><br />
  <a href="#72682">29-Jan-2007 07:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Function to truncate a string<br />
Removing dot and comma<br />
Adding ... only if a is character found<br />
<br />
function TruncateString(*phrase, *longueurMax = 150) {<br />

&nbsp;&nbsp;&nbsp; *phrase = substr(trim(*phrase), 0, *longueurMax);<br />
&nbsp;&nbsp;&nbsp; *pos = strrpos(*phrase, " ");<br />
&nbsp;&nbsp;&nbsp; *phrase = substr(*phrase, 0, *pos);<br />
&nbsp;&nbsp;&nbsp; if ((substr(*phrase,-1,1) == ",") or (substr(*phrase,-1,1) == ".")) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *phrase = substr(*phrase,0,-1);<br />

&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; if (*pos === false) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *phrase = *phrase;<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; else {<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *phrase = *phrase . "...";<br />
&nbsp;&nbsp;&nbsp; }<br />
return *phrase;<br />
}</span>
</code></div>
  </div>
 </div>

 <a name="72345"></a>
 <div class="note">
  <strong>Guilherme Garnier</strong><br />
  <a href="#72345">15-Jan-2007 11:44</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Actually, there is a little problem on your code: if *needle is not found inside *haystack, the function should return FALSE, but it is actually returning strlen(*haystack) - strlen(*needle). Here is a corrected version of it:<br />
<br />
<span class="default">&lt;?php<br />

</span><span class="keyword">function </span><span class="default">stringrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">,</span><span class="default">*offset</span><span class="keyword">=</span><span class="default">NULL</span><span class="keyword">)<br />
{<br />
&nbsp;&nbsp; if (</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">,</span><span class="default">*offset</span><span class="keyword">) === </span><span class="default">FALSE</span><span class="keyword">)<br />

&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">FALSE</span><span class="keyword">;<br />
<br />
&nbsp;&nbsp; return </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; - </span><span class="default">strpos</span><span class="keyword">( </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">) , </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) , </span><span class="default">*offset</span><span class="keyword">)<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; - </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>

  </div>
 </div>
 <a name="71882"></a>
 <div class="note">
  <strong>php NO at SPAMMERS willfris SREMMAPS dot ON nl</strong><br />
  <a href="#71882">21-Dec-2006 03:48</a>
  <div class="text">
<div class="phpcode"><code><span class="html">

<span class="default">&lt;?php<br />
</span><span class="comment">/*******<br />
&nbsp;** Maybe the shortest code to find the last occurence of a string, even in php4<br />
&nbsp;*******/<br />
</span><span class="keyword">function </span><span class="default">stringrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">,</span><span class="default">*offset</span><span class="keyword">=</span><span class="default">NULL</span><span class="keyword">)<br />

{<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; - </span><span class="default">strpos</span><span class="keyword">( </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">) , </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) , </span><span class="default">*offset</span><span class="keyword">)<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; - </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);<br />
}<br />
</span><span class="comment">// @return&nbsp;&nbsp; -&gt;&nbsp;&nbsp; chopped up for readability.<br />

</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="71401"></a>
 <div class="note">
  <strong>purpleidea</strong><br />
  <a href="#71401">27-Nov-2006 09:07</a>

  <div class="text">
<div class="phpcode"><code><span class="html">
I was having some issues when I moved my code to run it on a different server.<br />
The earlier php version didn't support more than one character needles, so tada, bugs. It's in the docs, i'm just pointing it out in case you're scratching your head for a while.</span>
</code></div>
  </div>
 </div>
 <a name="70925"></a>
 <div class="note">
  <strong>dmitry dot polushkin at gmail dot com</strong><br />

  <a href="#70925">04-Nov-2006 07:05</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Returns the filename's string extension, else if no extension found returns false.<br />
Example: filename_extension('some_file.mp3'); // mp3<br />
Faster than the pathinfo() analogue in two times.<br />
<span class="default">&lt;?php<br />
</span><span class="keyword">function </span><span class="default">filename_extension</span><span class="keyword">(</span><span class="default">*filename</span><span class="keyword">) {<br />

&nbsp;&nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*filename</span><span class="keyword">, </span><span class="string">'.'</span><span class="keyword">);<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">*pos</span><span class="keyword">===</span><span class="default">false</span><span class="keyword">) {<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; } else {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">substr</span><span class="keyword">(</span><span class="default">*filename</span><span class="keyword">, </span><span class="default">*pos</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">);<br />

&nbsp;&nbsp;&nbsp; }<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="67559"></a>
 <div class="note">

  <strong>kavih7 at yahoo dot com</strong><br />
  <a href="#67559">08-Jun-2006 09:53</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
<span class="default">&lt;?php
<br />
</span><span class="comment">###################################################
<br />
#
<br />
# DESCRIPTION:
<br />

# This function returns the last occurance of a string,
<br />
# rather than the last occurance of a single character like
<br />
# strrpos does. It also supports an offset from where to
<br />
# start the searching in the haystack string.
<br />
#
<br />
# ARGS:
<br />
# *haystack (required) -- the string to search upon
<br />
# *needle (required) -- the string you are looking for
<br />
# *offset (optional) -- the offset to start from

<br />
#
<br />
# RETURN VALS:
<br />
# returns integer on success
<br />
# returns false on failure to find the string at all
<br />
#
<br />
###################################################
<br />

<br />
</span><span class="keyword">function </span><span class="default">strrpos_string</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*offset </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">)

<br />
{
<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">trim</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">) != </span><span class="string">"" </span><span class="keyword">&amp;&amp; </span><span class="default">trim</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) != </span><span class="string">"" </span><span class="keyword">&amp;&amp; </span><span class="default">*offset </span><span class="keyword">&lt;= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">))

<br />
&nbsp;&nbsp;&nbsp; {
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*last_pos </span><span class="keyword">= </span><span class="default">*offset</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*found </span><span class="keyword">= </span><span class="default">false</span><span class="keyword">;

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while((</span><span class="default">*curr_pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*last_pos</span><span class="keyword">)) !== </span><span class="default">false</span><span class="keyword">)

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*found </span><span class="keyword">= </span><span class="default">true</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*last_pos </span><span class="keyword">= </span><span class="default">*curr_pos </span><span class="keyword">+ </span><span class="default">1</span><span class="keyword">;

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">*found</span><span class="keyword">)
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">*last_pos </span><span class="keyword">- </span><span class="default">1</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }
<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; else
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }
<br />
&nbsp;&nbsp;&nbsp; }
<br />
&nbsp;&nbsp;&nbsp; else 
<br />
&nbsp;&nbsp;&nbsp; {
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;

<br />
&nbsp;&nbsp;&nbsp; }
<br />
}
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="65569"></a>
 <div class="note">

  <strong>shimon at schoolportal dot co dot il</strong><br />
  <a href="#65569">03-May-2006 08:31</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In strrstr function in php 4 there is also no offset.<br />
<span class="default">&lt;?<br />
</span><span class="comment">// by Shimon Doodkin<br />
</span><span class="keyword">function </span><span class="default">chrrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*offset</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">)<br />

{<br />
&nbsp;</span><span class="default">*needle</span><span class="keyword">=</span><span class="default">*needle</span><span class="keyword">[</span><span class="default">0</span><span class="keyword">];<br />
&nbsp;</span><span class="default">*l</span><span class="keyword">=</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);<br />
&nbsp;if(</span><span class="default">*l</span><span class="keyword">==</span><span class="default">0</span><span class="keyword">)&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />

&nbsp;if(</span><span class="default">*offset</span><span class="keyword">===</span><span class="default">false</span><span class="keyword">)&nbsp; </span><span class="default">*offset</span><span class="keyword">=</span><span class="default">*l</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">;<br />
&nbsp;else<br />
&nbsp;{<br />

&nbsp; if(</span><span class="default">*offset</span><span class="keyword">&gt;</span><span class="default">*l</span><span class="keyword">) </span><span class="default">*offset</span><span class="keyword">=</span><span class="default">*l</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">;<br />
&nbsp; if(</span><span class="default">*offset</span><span class="keyword">&lt;</span><span class="default">0</span><span class="keyword">) return </span><span class="default">false</span><span class="keyword">;<br />

&nbsp;}<br />
&nbsp;for(;</span><span class="default">*offset</span><span class="keyword">&gt;</span><span class="default">0</span><span class="keyword">;</span><span class="default">*offset</span><span class="keyword">--)<br />
&nbsp; if(</span><span class="default">*haystack</span><span class="keyword">[</span><span class="default">*offset</span><span class="keyword">]==</span><span class="default">*needle</span><span class="keyword">)<br />

&nbsp;&nbsp; return </span><span class="default">*offset</span><span class="keyword">;<br />
&nbsp;return </span><span class="default">false</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>

 </div>
 <a name="61432"></a>
 <div class="note">
  <strong>clan_ghw2 at hotmail dot com</strong><br />
  <a href="#61432">03-Feb-2006 08:28</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Brian below is incorrect about strrpos on different platforms.<br />

<br />
Tested on Home PC (win32 + PHP 5.1.2) and Web Server (linux + 4.4.1)<br />
<br />
echo strrpos("blah.blahannila","blaha");<br />
returns 5 on windows<br />
returns 5 on linux<br />
<br />
Could've been a bug with an earlier PHP version, however the latest version of PHP returns position of the beginning of the string we're trying to find.<br />
<br />
-Thaddeus</span>
</code></div>

  </div>
 </div>
 <a name="57043"></a>
 <div class="note">
  <strong>nh_handyman</strong><br />
  <a href="#57043">22-Sep-2005 12:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">

As noted in some examples below, strrpos does not act the same on every platform!<br />
<br />
On Linux, it returns the position of the end of the target<br />
On Windows, it returns the position of the start of the target<br />
<br />
strrpos ("c:/somecity/html/t.php")<br />
<br />
returns 11 on Windows<br />
returns 16 on Linux<br />
<br />
Brian</span>

</code></div>
  </div>
 </div>
 <a name="56735"></a>
 <div class="note">
  <strong>gordon at kanazawa-gu dot ac dot jp</strong><br />
  <a href="#56735">14-Sep-2005 06:56</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
The "find-last-occurrence-of-a-string" functions suggested here do not allow for a starting offset, so here's one, tried and tested, that does:<br />
<br />
function my_strrpos(*haystack, *needle, *offset=0) {<br />
&nbsp;&nbsp;&nbsp; // same as strrpos, except *needle can be a string<br />
&nbsp;&nbsp;&nbsp; *strrpos = false;<br />
&nbsp;&nbsp;&nbsp; if (is_string(*haystack) &amp;&amp; is_string(*needle) &amp;&amp; is_numeric(*offset)) {<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *strlen = strlen(*haystack);<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *strpos = strpos(strrev(substr(*haystack, *offset)), strrev(*needle));<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (is_numeric(*strpos)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; *strrpos = *strlen - *strpos - strlen(*needle);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; }<br />
&nbsp;&nbsp;&nbsp; return *strrpos;<br />
}</span>
</code></div>
  </div>
 </div>

 <a name="56069"></a>
 <div class="note">
  <strong>genetically altered mastermind at gmail</strong><br />
  <a href="#56069">22-Aug-2005 07:30</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Very handy to get a file extension:<br />
*this-&gt;data['extension'] = substr(*this-&gt;data['name'],strrpos(*this-&gt;data['name'],'.')+1);</span>

</code></div>
  </div>
 </div>
 <a name="55670"></a>
 <div class="note">
  <strong>fab</strong><br />
  <a href="#55670">10-Aug-2005 01:07</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
RE: hao2lian<br />
<br />
There are a lot of alternative - and unfortunately buggy - implementations of strrpos() (or last_index_of as it was called) on this page. This one is a slight modifiaction of the one below, but it should world like a *real* strrpos(), because it returns false if there is no needle in the haystack.<br />
<br />
<span class="default">&lt;?php<br />
<br />
</span><span class="keyword">function </span><span class="default">my_strrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">) {<br />

&nbsp;&nbsp; </span><span class="default">*index </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">), </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));<br />
&nbsp;&nbsp; if(</span><span class="default">*index </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) {<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">false</span><span class="keyword">;<br />
&nbsp;&nbsp; }<br />
&nbsp;&nbsp; </span><span class="default">*index </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">) - </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) - </span><span class="default">*index</span><span class="keyword">;<br />

&nbsp;&nbsp; return </span><span class="default">*index</span><span class="keyword">;<br />
}<br />
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="55547"></a>

 <div class="note">
  <strong>lwoods</strong><br />
  <a href="#55547">06-Aug-2005 09:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
If you are a VBScript programmer ("ex-" of course), you will find that 'strrpos' doesn't work like the VBScript 'instrRev' function.<br />
<br />
Here is the equivalent function:<br />
<br />

VBScript:<br />
<br />
k=instrrev(s,"&gt;",j);<br />
<br />
PHP Equivalent of the above VBScript:<br />
<br />
*k=strrpos(substr(*s,0,*j),'&gt;');<br />
<br />
Comments:<br />
<br />

You might think (I did!) that the following PHP function call would be the equivant of the above VBScript call:<br />
<br />
*kk=strrpos(*s,'&gt;',*j);<br />
<br />
NOPE!&nbsp; In the above PHP call, *j defines the position in the string that should be considered the BEGINNING of the string, whereas in the VBScript call, j is to be considered the END of the string, as far as this search is concerned.&nbsp; Anyway, the above 'strrpos' with the 'substr' will work.<br />
(Probably faster to write a for loop!)</span>
</code></div>
  </div>

 </div>
 <a name="55418"></a>
 <div class="note">
  <strong>hao2lian</strong><br />
  <a href="#55418">03-Aug-2005 04:50</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Yet another correction on the last_index_of function algorithm:<br />

<br />
function last_index_of(*haystack, *needle) {<br />
&nbsp;&nbsp;&nbsp; *index = strpos(strrev(*haystack), strrev(*needle));<br />
&nbsp;&nbsp;&nbsp; *index = strlen(*haystack) - strlen(*needle) - *index;<br />
&nbsp;&nbsp;&nbsp; return *index;<br />
}<br />
<br />
"strlen(index)" in the most recent one should be "strlen(*needle)".</span>

</code></div>
  </div>
 </div>
 <a name="51636"></a>
 <div class="note">
  <strong>jonas at jonasbjork dot net</strong><br />
  <a href="#51636">06-Apr-2005 10:25</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
I needed to remove last directory from an path, and came up with this solution:<br />
<br />
<span class="default">&lt;?php<br />
<br />
&nbsp; *path_dir </span><span class="keyword">= </span><span class="string">"/my/sweet/home/"</span><span class="keyword">;<br />
&nbsp; </span><span class="default">*path_up </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">( </span><span class="default">*path_dir</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">, </span><span class="default">strrpos</span><span class="keyword">( </span><span class="default">*path_dir</span><span class="keyword">, </span><span class="string">'/'</span><span class="keyword">, -</span><span class="default">2 </span><span class="keyword">) ).</span><span class="string">"/"</span><span class="keyword">;<br />

&nbsp; echo </span><span class="default">*path_up</span><span class="keyword">;<br />
<br />
</span><span class="default">?&gt;<br />
</span><br />
Might be helpful for someone..</span>
</code></div>
  </div>
 </div>
 <a name="50746"></a>

 <div class="note">
  <a href="#50746">08-Mar-2005 08:14</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
In the below example, it should be substr, not strrpos.<br />
<br />
&lt;PHP?<br />
<br />
*filename = substr(*url, strrpos(*url, '/') + 1);<br />
<br />

?&gt;</span>
</code></div>
  </div>
 </div>
 <a name="48886"></a>
 <div class="note">
  <strong>escii at hotmail dot com ( Brendan )</strong><br />
  <a href="#48886">11-Jan-2005 04:12</a>

  <div class="text">
<div class="phpcode"><code><span class="html">
I was immediatley pissed when i found the behaviour of strrpos ( shouldnt it be called charrpos ?) the way it is, so i made my own implement to search for strings.<br />
<br />
<span class="default">&lt;?<br />
</span><span class="keyword">function </span><span class="default">proper_strrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">){<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; while(</span><span class="default">*ret </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">))<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; {&nbsp; &nbsp; &nbsp;&nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">strncmp</span><span class="keyword">(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*ret</span><span class="keyword">,</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">)),<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*needle</span><span class="keyword">,</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">)) == </span><span class="default">0 </span><span class="keyword">)<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">*ret</span><span class="keyword">;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*haystack </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">0</span><span class="keyword">,</span><span class="default">*ret </span><span class="keyword">-</span><span class="default">1 </span><span class="keyword">);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">*ret</span><span class="keyword">;<br />
}<br />
</span><span class="default">?&gt;</span>
</span>

</code></div>
  </div>
 </div>
 <a name="47482"></a>
 <div class="note">
  <strong>griffioen at justdesign dot nl</strong><br />
  <a href="#47482">17-Nov-2004 07:57</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
If you wish to look for the last occurrence of a STRING in a string (instead of a single character) and don't have mb_strrpos working, try this:<br />
<br />
&nbsp;&nbsp;&nbsp; function lastIndexOf(*haystack, *needle) {<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *index&nbsp; &nbsp; &nbsp; &nbsp; = strpos(strrev(*haystack), strrev(*needle));<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *index&nbsp; &nbsp; &nbsp; &nbsp; = strlen(*haystack) - strlen(index) - *index;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return *index;<br />
&nbsp;&nbsp;&nbsp; }</span>

</code></div>
  </div>
 </div>
 <a name="46342"></a>
 <div class="note">
  <strong>nexman at playoutloud dot net</strong><br />
  <a href="#46342">07-Oct-2004 06:22</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
Function like the 5.0 version of strrpos for 4.x.<br />
This will return the *last* occurence of a string within a string.<br />
<br />
&nbsp;&nbsp;&nbsp; function strepos(*haystack, *needle, *offset=0) {&nbsp; &nbsp; &nbsp; &nbsp; <br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *pos_rule = (*offset&lt;0)?strlen(*haystack)+(*offset-1):*offset;<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; *last_pos = false; *first_run = true;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; do {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; *pos=strpos(*haystack, *needle, (intval(*last_pos)+((*first_run)?0:strlen(*needle))));<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (*pos!==false &amp;&amp; ((*offset&lt;0 &amp;&amp; *pos &lt;= *pos_rule)||*offset &gt;= 0)) {<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; *last_pos = *pos;<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; } else { break; }<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; *first_run = false;<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } while (*pos !== false);<br />

&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (*offset&gt;0 &amp;&amp; *last_pos&lt;*pos_rule) { *last_pos = false; }<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return *last_pos;<br />
&nbsp;&nbsp;&nbsp; }<br />

<br />
If my math is off, please feel free to correct.<br />
&nbsp; - A positive offset will be the minimum character index position of the first character allowed.<br />
&nbsp; - A negative offset will be subtracted from the total length and the position directly before will be the maximum index of the first character being searched.<br />
<br />
returns the character index ( 0+ ) of the last occurence of the needle. <br />
<br />
* boolean FALSE will return no matches within the haystack, or outside boundries specified by the offset.</span>
</code></div>
  </div>

 </div>
 <a name="42689"></a>
 <div class="note">
  <strong>harlequin AT gmx DOT de</strong><br />
  <a href="#42689">26-May-2004 06:59</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this is my function for finding a filename in a URL:
<br />

<br />
<span class="default">&lt;?php
<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">function </span><span class="default">getfname</span><span class="keyword">(</span><span class="default">*url</span><span class="keyword">){
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*url</span><span class="keyword">, </span><span class="string">"/"</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">*pos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// not found / no filename in url...
<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">false</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; } else {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// Get the string length

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*len </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*url</span><span class="keyword">);
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; if (</span><span class="default">*len </span><span class="keyword">&lt; </span><span class="default">*pos</span><span class="keyword">){

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; print </span><span class="string">"*len / *pos"</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">// the last slash we found belongs to <a href="http://" rel="nofollow" target="_blank">http://</a> or it is the trailing slash of a URL

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">false</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; } else {

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*filename </span><span class="keyword">= </span><span class="default">substr</span><span class="keyword">(</span><span class="default">*url</span><span class="keyword">, </span><span class="default">*pos</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">, </span><span class="default">*len</span><span class="keyword">-</span><span class="default">*pos</span><span class="keyword">-</span><span class="default">1</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; }
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return </span><span class="default">*filename</span><span class="keyword">;

<br />
&nbsp;&nbsp;&nbsp; }
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="42637"></a>
 <div class="note">
  <strong>tsa at medicine dot wisc dot edu</strong><br />

  <a href="#42637">25-May-2004 02:17</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
What the heck, I thought I'd throw another function in the mix.&nbsp; It's not pretty but the following function counts backwards from your starting point and tells you the last occurrance of a mixed char string:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="keyword">function </span><span class="default">strrposmixed </span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*start</span><span class="keyword">=</span><span class="default">0</span><span class="keyword">) {

<br />
&nbsp;&nbsp; </span><span class="comment">// init start as the end of the str if not set
<br />
&nbsp;&nbsp; </span><span class="keyword">if(</span><span class="default">*start </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">*start </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">);

<br />
&nbsp;&nbsp; }
<br />
&nbsp;&nbsp; 
<br />
&nbsp;&nbsp; </span><span class="comment">// searches backward from *start
<br />
&nbsp;&nbsp; </span><span class="default">*currentStrPos</span><span class="keyword">=</span><span class="default">*start</span><span class="keyword">;
<br />
&nbsp;&nbsp; </span><span class="default">*lastFoundPos</span><span class="keyword">=</span><span class="default">false</span><span class="keyword">;

<br />
&nbsp;&nbsp; 
<br />
&nbsp;&nbsp; while(</span><span class="default">*currentStrPos </span><span class="keyword">!= </span><span class="default">0</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; if(!(</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">,</span><span class="default">*currentStrPos</span><span class="keyword">) === </span><span class="default">false</span><span class="keyword">)) {

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span class="default">*lastFoundPos</span><span class="keyword">=</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">,</span><span class="default">*currentStrPos</span><span class="keyword">);
<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; break;
<br />
&nbsp;&nbsp; &nbsp; &nbsp; }
<br />
&nbsp;&nbsp; &nbsp; &nbsp; </span><span class="default">*currentStrPos</span><span class="keyword">--;

<br />
&nbsp;&nbsp; }
<br />
&nbsp;&nbsp; 
<br />
&nbsp;&nbsp; if(</span><span class="default">*lastFoundPos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; return </span><span class="default">false</span><span class="keyword">;

<br />
&nbsp;&nbsp; } else {
<br />
&nbsp;&nbsp; &nbsp; &nbsp; return </span><span class="default">*lastFoundPos</span><span class="keyword">;
<br />
&nbsp;&nbsp; }
<br />
}
<br />
</span><span class="default">?&gt;</span>

</span>
</code></div>
  </div>
 </div>
 <a name="39613"></a>
 <div class="note">
  <strong>dreamclub2000 at hotmail dot com</strong><br />
  <a href="#39613">04-Feb-2004 09:17</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
This function does what strrpos would if it handled multi-character strings:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="keyword">function </span><span class="default">getLastStr</span><span class="keyword">(</span><span class="default">*hay</span><span class="keyword">, </span><span class="default">*need</span><span class="keyword">){
<br />
&nbsp; </span><span class="default">*getLastStr </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">;

<br />
&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*hay</span><span class="keyword">, </span><span class="default">*need</span><span class="keyword">);
<br />
&nbsp; if (</span><span class="default">is_int </span><span class="keyword">(</span><span class="default">*pos</span><span class="keyword">)){ </span><span class="comment">//this is to decide whether it is "false" or "0"

<br />
&nbsp;&nbsp;&nbsp; </span><span class="keyword">while(</span><span class="default">*pos</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*getLastStr </span><span class="keyword">= </span><span class="default">*getLastStr </span><span class="keyword">+ </span><span class="default">*pos </span><span class="keyword">+ </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*need</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*hay </span><span class="keyword">= </span><span class="default">substr </span><span class="keyword">(</span><span class="default">*hay </span><span class="keyword">, </span><span class="default">*pos </span><span class="keyword">+ </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*need</span><span class="keyword">));
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*hay</span><span class="keyword">, </span><span class="default">*need</span><span class="keyword">);

<br />
&nbsp;&nbsp;&nbsp; }
<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">*getLastStr </span><span class="keyword">- </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*need</span><span class="keyword">);
<br />
&nbsp; } else {
<br />
&nbsp;&nbsp;&nbsp; return -</span><span class="default">1</span><span class="keyword">; </span><span class="comment">//if *need wasn?t found it returns "-1" , because it could return "0" if it?s found on position "0".

<br />
&nbsp; </span><span class="keyword">}
<br />
}
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="36548"></a>
 <div class="note">

  <strong>ZaraWebFX</strong><br />
  <a href="#36548">14-Oct-2003 08:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
this could be, what derek mentioned:<br />
<br />
<span class="default">&lt;?<br />
</span><span class="keyword">function </span><span class="default">cut_last_occurence</span><span class="keyword">(</span><span class="default">*string</span><span class="keyword">,</span><span class="default">*cut_off</span><span class="keyword">) {<br />

&nbsp;&nbsp;&nbsp; return </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">substr</span><span class="keyword">(</span><span class="default">strstr</span><span class="keyword">(</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*string</span><span class="keyword">), </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*cut_off</span><span class="keyword">)),</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*cut_off</span><span class="keyword">)));<br />

}&nbsp; &nbsp; <br />
<br />
</span><span class="comment">//&nbsp; &nbsp; example: cut off the last occurence of "limit"<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*str </span><span class="keyword">= </span><span class="string">"select delta_limit1, delta_limit2, delta_limit3 from table limit 1,7"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*search </span><span class="keyword">= </span><span class="string">" limit"</span><span class="keyword">;<br />

&nbsp;&nbsp;&nbsp; echo </span><span class="default">*str</span><span class="keyword">.</span><span class="string">"\n"</span><span class="keyword">;<br />
&nbsp;&nbsp;&nbsp; echo </span><span class="default">cut_last_occurence</span><span class="keyword">(</span><span class="default">*str</span><span class="keyword">,</span><span class="string">"limit"</span><span class="keyword">);<br />
</span><span class="default">?&gt;</span>

</span>
</code></div>
  </div>
 </div>
 <a name="35387"></a>
 <div class="note">
  <strong>lee at 5ss dot net</strong><br />
  <a href="#35387">29-Aug-2003 04:21</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
I should have looked here first, but instead I wrote my own version of strrpos that supports searching for entire strings, rather than individual characters.&nbsp; This is a recursive function.&nbsp; I have not tested to see if it is more or less efficient than the others on the page.&nbsp; I hope this helps someone!
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="comment">//Find last occurance of needle in haystack
<br />
</span><span class="keyword">function </span><span class="default">str_rpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*start </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">){

<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*tempPos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*start</span><span class="keyword">);
<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">*tempPos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">){

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if(</span><span class="default">*start </span><span class="keyword">== </span><span class="default">0</span><span class="keyword">){
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Needle not in string at all
<br />

&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">false</span><span class="keyword">;
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }else{
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//No more occurances found

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">*start </span><span class="keyword">- </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">);
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; }

<br />
&nbsp;&nbsp;&nbsp; }else{
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="comment">//Find the next occurance
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">return </span><span class="default">str_rpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*tempPos </span><span class="keyword">+ </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));

<br />
&nbsp;&nbsp;&nbsp; }
<br />
}
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="34021"></a>
 <div class="note">

  <strong>ara at bluemedia dot us</strong><br />
  <a href="#34021">14-Jul-2003 10:09</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
derek@slashview.com notes a great replacement for strrpos because of the single character needle limitation in the strrpos function. He made a slight error in the code. He adds the length of the needle string instead of subtracting it from the final position. The function should be:
<br />

<br />
<span class="default">&lt;?php
<br />

</span><span class="keyword">function </span><span class="default">strlastpos</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">) {
<br />
</span><span class="comment"># flip both strings around and search, then adjust position based on string lengths
<br />
</span><span class="keyword">return </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">) - </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">) - </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">), </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));

<br />
}
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="31506"></a>
 <div class="note">
  <strong>no_spammage_at_wwwcrm_dot_com</strong><br />

  <a href="#31506">24-Apr-2003 05:07</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
This function does what strrpos would if it handled multi-character strings:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="comment">//function recurses until it finds last instance of *needle in *haystack
<br />

<br />
</span><span class="keyword">function </span><span class="default">getLastStr</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">*first_time</span><span class="keyword">=</span><span class="default">1</span><span class="keyword">){

<br />

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*test</span><span class="keyword">=</span><span class="default">strstr</span><span class="keyword">(</span><span class="default">*haystack</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">);</span><span class="comment">//is the needle there?

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">if (</span><span class="default">*test</span><span class="keyword">) return </span><span class="default">getLastStr</span><span class="keyword">(</span><span class="default">*test</span><span class="keyword">, </span><span class="default">*needle</span><span class="keyword">, </span><span class="default">0</span><span class="keyword">);</span><span class="comment">//see if there is another one?

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">else if (</span><span class="default">*first_time</span><span class="keyword">) return </span><span class="default">false</span><span class="keyword">;</span><span class="comment">//there is no occurence at all
<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">else return </span><span class="default">*haystack</span><span class="keyword">;</span><span class="comment">//that was the last occurence

<br />

<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; </span><span class="keyword">}
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>

 </div>
 <a name="29495"></a>
 <div class="note">
  <strong>FIE</strong><br />
  <a href="#29495">15-Feb-2003 02:03</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
refering to the comment and function about lastIndexOf()...
<br />

It seemed not to work for me the only reason I could find was the haystack was reversed and the string wasnt therefore it returnt the length of the haystack rather than the position of the last needle... i rewrote it as fallows:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="keyword">function </span><span class="default">strlpos</span><span class="keyword">(</span><span class="default">*f_haystack</span><span class="keyword">,</span><span class="default">*f_needle</span><span class="keyword">) {
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*rev_str </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*f_needle</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*rev_hay </span><span class="keyword">= </span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*f_haystack</span><span class="keyword">);
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*hay_len </span><span class="keyword">= </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*f_haystack</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*ned_pos </span><span class="keyword">= </span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*rev_hay</span><span class="keyword">,</span><span class="default">*rev_str</span><span class="keyword">);
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*result&nbsp; </span><span class="keyword">= </span><span class="default">*hay_len </span><span class="keyword">- </span><span class="default">*ned_pos </span><span class="keyword">- </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*rev_str</span><span class="keyword">);

<br />
&nbsp;&nbsp; &nbsp;&nbsp; return </span><span class="default">*result</span><span class="keyword">;
<br />
}
<br />
</span><span class="default">?&gt;
<br />
</span>
<br />
this one fallows the strpos syntax rather than java's lastIndexOf.
<br />
I'm not positive if it takes more resources assigning all of those variables in there but you can put it all in return if you want, i dont care if i crash my server ;).

<br />

<br />
~SILENT WIND OF DOOM WOOSH!</span>
</code></div>
  </div>
 </div>
 <a name="28745"></a>
 <div class="note">
  <strong>rob at pulpchat dot com</strong><br />

  <a href="#28745">22-Jan-2003 11:23</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
For those of you coming from VBScript, I have 
<br />
converted the instrrev function to PHP:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="keyword">function </span><span class="default">instrrev</span><span class="keyword">(</span><span class="default">*n</span><span class="keyword">,</span><span class="default">*s</span><span class="keyword">) {

<br />
&nbsp; </span><span class="default">*x</span><span class="keyword">=</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">chr</span><span class="keyword">(</span><span class="default">0</span><span class="keyword">).</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*n</span><span class="keyword">),</span><span class="default">*s</span><span class="keyword">)+</span><span class="default">0</span><span class="keyword">;

<br />
&nbsp; return ((</span><span class="default">*x</span><span class="keyword">==</span><span class="default">0</span><span class="keyword">) ? </span><span class="default">0 </span><span class="keyword">: </span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*n</span><span class="keyword">)-</span><span class="default">*x</span><span class="keyword">+</span><span class="default">1</span><span class="keyword">);

<br />
}
<br />
</span><span class="default">?&gt;
<br />
</span>
<br />
Remember that, unlike PHP and Javascript, VBScript 
<br />
returns 0 for no string found and 1 for the first 
<br />
character position, etc.
<br />

<br />
Hopefully this will save some time if you are 

<br />
converting ASP pages to PHP.</span>
</code></div>
  </div>
 </div>
 <a name="27750"></a>
 <div class="note">
  <strong>php dot net at insite-out dot com</strong><br />
  <a href="#27750">17-Dec-2002 08:47</a>

  <div class="text">
<div class="phpcode"><code><span class="html">
I was looking for the equivalent of Java's lastIndexOf(). I couldn't find it so I wrote this:
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="comment">/*
<br />
Method to return the last occurrence of a substring within a 
<br />
string
<br />
*/
<br />

</span><span class="keyword">function </span><span class="default">last_index_of</span><span class="keyword">(</span><span class="default">*sub_str</span><span class="keyword">,</span><span class="default">*instr</span><span class="keyword">) {
<br />
&nbsp;&nbsp;&nbsp; if(</span><span class="default">strstr</span><span class="keyword">(</span><span class="default">*instr</span><span class="keyword">,</span><span class="default">*sub_str</span><span class="keyword">)!=</span><span class="string">""</span><span class="keyword">) {

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; return(</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*instr</span><span class="keyword">)-</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">strrev</span><span class="keyword">(</span><span class="default">*instr</span><span class="keyword">),</span><span class="default">*sub_str</span><span class="keyword">));

<br />
&nbsp;&nbsp;&nbsp; }
<br />
&nbsp;&nbsp;&nbsp; return(-</span><span class="default">1</span><span class="keyword">);
<br />
}
<br />
</span><span class="default">?&gt;
<br />
</span>
<br />
It returns the numerical index of the substring you're searching for, or -1 if the substring doesn't exist within the string.</span>

</code></div>
  </div>
 </div>
 <a name="27634"></a>
 <div class="note">
  <strong>su.noseelg@naes, only backwards</strong><br />
  <a href="#27634">13-Dec-2002 07:39</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
Maybe I'm the only one who's bothered by it, but it really bugs me when the last line in a paragraph is a single word. Here's an example to explain what I don't like:
<br />

<br />
The quick brown fox jumps over the lazy
<br />
dog.
<br />

<br />
So that's why I wrote this function. In any paragraph that contains more than 1 space (i.e., more than two words), it will replace the last space with '&amp;nbsp;'.
<br />

<br />
<span class="default">&lt;?php

<br />
</span><span class="keyword">function </span><span class="default">no_orphans</span><span class="keyword">(</span><span class="default">*TheParagraph</span><span class="keyword">) {
<br />
&nbsp;&nbsp;&nbsp; if (</span><span class="default">substr_count</span><span class="keyword">(</span><span class="default">*TheParagraph</span><span class="keyword">,</span><span class="string">" "</span><span class="keyword">) &gt; </span><span class="default">1</span><span class="keyword">) {

<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*lastspace </span><span class="keyword">= </span><span class="default">strrpos</span><span class="keyword">(</span><span class="default">*TheParagraph</span><span class="keyword">,</span><span class="string">" "</span><span class="keyword">);
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*TheParagraph </span><span class="keyword">= </span><span class="default">substr_replace</span><span class="keyword">(</span><span class="default">*TheParagraph</span><span class="keyword">,</span><span class="string">"&amp;nbsp;"</span><span class="keyword">,</span><span class="default">*lastspace</span><span class="keyword">,</span><span class="default">1</span><span class="keyword">);

<br />
&nbsp;&nbsp;&nbsp; }
<br />
return </span><span class="default">*TheParagraph</span><span class="keyword">;
<br />
}
<br />
</span><span class="default">?&gt;
<br />
</span>
<br />
So, it would change "The quick brown fox jumps over the lazy dog." to "The quick brown fox jumps over the lazy&amp;nbsp;dog." That way, the last two words will always stay together.</span>

</code></div>
  </div>
 </div>
 <a name="26437"></a>
 <div class="note">
  <strong>DONT SPAM vardges at iqnest dot com</strong><br />
  <a href="#26437">30-Oct-2002 10:22</a>
  <div class="text">

<div class="phpcode"><code><span class="html">
that function can be modified to this
<br />

<br />
<span class="default">&lt;?php
<br />
</span><span class="keyword">function </span><span class="default">strrpos_str </span><span class="keyword">(</span><span class="default">*string</span><span class="keyword">, </span><span class="default">*searchFor</span><span class="keyword">, </span><span class="default">*startFrom </span><span class="keyword">= </span><span class="default">0</span><span class="keyword">)

<br />
{
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*addLen </span><span class="keyword">= </span><span class="default">strlen </span><span class="keyword">(</span><span class="default">*searchFor</span><span class="keyword">);
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*endPos </span><span class="keyword">= </span><span class="default">*startFrom </span><span class="keyword">- </span><span class="default">*addLen</span><span class="keyword">;

<br />

<br />
&nbsp;&nbsp;&nbsp; while (</span><span class="default">true</span><span class="keyword">)
<br />
&nbsp;&nbsp;&nbsp; {
<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; if ((</span><span class="default">*newPos </span><span class="keyword">= </span><span class="default">strpos </span><span class="keyword">(</span><span class="default">*string</span><span class="keyword">, </span><span class="default">*searchFor</span><span class="keyword">, </span><span class="default">*endPos </span><span class="keyword">+ </span><span class="default">*addLen</span><span class="keyword">)) === </span><span class="default">false</span><span class="keyword">) break;

<br />
&nbsp;&nbsp; &nbsp; &nbsp;&nbsp; </span><span class="default">*endPos </span><span class="keyword">= </span><span class="default">*newPos</span><span class="keyword">;
<br />
&nbsp;&nbsp;&nbsp; }
<br />

<br />
&nbsp;&nbsp;&nbsp; return (</span><span class="default">*endPos </span><span class="keyword">&gt;= </span><span class="default">0</span><span class="keyword">) ? </span><span class="default">*endPos </span><span class="keyword">: </span><span class="default">false</span><span class="keyword">;

<br />
}
<br />

<br />
</span><span class="comment">// example
<br />
</span><span class="default">*str </span><span class="keyword">= </span><span class="string">"abcabcabc"</span><span class="keyword">;
<br />
</span><span class="default">*search </span><span class="keyword">= </span><span class="string">"ab"</span><span class="keyword">;
<br />

<br />
</span><span class="default">*pos </span><span class="keyword">= </span><span class="default">strrpos_str </span><span class="keyword">(</span><span class="default">*str</span><span class="keyword">, </span><span class="default">*search</span><span class="keyword">);
<br />
if (</span><span class="default">*pos </span><span class="keyword">=== </span><span class="default">false</span><span class="keyword">) echo </span><span class="string">"not found"</span><span class="keyword">;

<br />
else echo </span><span class="default">*pos</span><span class="keyword">; </span><span class="comment">// returns 6 in this case
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="21825"></a>
 <div class="note">

  <a href="#21825">28-May-2002 09:46</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
Cause:
<br />
Find position of last occurrence of a string in a string... 
<br />
and I needed it, I hacked a little code to do this:
<br />

<br />
Maybe it is helpful for you.
<br />

<br />
<span class="default">&lt;?php
<br />
&nbsp;</span><span class="keyword">function </span><span class="default">_strrpos_needle</span><span class="keyword">(</span><span class="default">*sourcestring</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">){
<br />

<br />
&nbsp;&nbsp;&nbsp; </span><span class="comment">/* just for easier understanding */
<br />
&nbsp;&nbsp;&nbsp; </span><span class="default">*tempString</span><span class="keyword">=</span><span class="default">*sourcestring</span><span class="keyword">;

<br />

<br />
&nbsp;&nbsp;&nbsp; do {
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*tempPos</span><span class="keyword">=</span><span class="default">strpos</span><span class="keyword">(</span><span class="default">*tempString</span><span class="keyword">,</span><span class="default">*needle</span><span class="keyword">);
<br />

&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*tempString</span><span class="keyword">=</span><span class="default">substr</span><span class="keyword">(</span><span class="default">*tempString</span><span class="keyword">,</span><span class="default">*tempPos</span><span class="keyword">+</span><span class="default">strlen</span><span class="keyword">(</span><span class="default">*needle</span><span class="keyword">));
<br />
&nbsp;&nbsp; &nbsp;&nbsp; </span><span class="default">*realPos</span><span class="keyword">=</span><span class="default">*realPos</span><span class="keyword">+</span><span class="default">*tempPos</span><span class="keyword">;

<br />
&nbsp;&nbsp;&nbsp; } while (!</span><span class="default">is_bool</span><span class="keyword">(</span><span class="default">*tempPos</span><span class="keyword">));
<br />

<br />
&nbsp;&nbsp;&nbsp; return </span><span class="default">*realPos</span><span class="keyword">;
<br />

<br />

&nbsp; }
<br />
</span><span class="default">?&gt;</span>
</span>
</code></div>
  </div>
 </div>
 <a name="18720"></a>
 <div class="note">
  <strong>derek at slashview dot com</strong><br />

  <a href="#18720">02-Feb-2002 01:06</a>
  <div class="text">
<div class="phpcode"><code><span class="html">
To find the position of the start of the last occurence of a string, we can do this:<br />
*pos=strlen(*haystack) - (strpos(strrev(*haystack), strrev(*needle)) + strlen(*needle));<br />
The idea is to reverse both *needle and *haystack, use strpos to find the first occurence of *needle in *haystack, then count backwards by the length of *needle. Finally, subtract *pos from length of *haystack. A lot easier to figure out if you use a test string to visualize it.&nbsp; :)</span>
</code></div>
  </div>

 </div></div>

 <div class="foot"><a href="/manual/add-note.php?sect=function.strrpos&amp;redirect=http://fr.php.net/manual/en/function.strrpos.php"><img src="/images/notes-add.gif" alt="add a note" width="13" height="13" class="middle" /></a> <small><a href="/manual/add-note.php?sect=function.strrpos&amp;redirect=http://fr.php.net/manual/en/function.strrpos.php">add a note</a></small></div>
</div><br /><!--UdmComment-->
<div class="manualnavbar manualnavbar_bottom">
 <span class="next">
  <a href="function.strspn.php">strspn<img src="/images/caret-r.gif" alt="&gt;" width="11" height="7" /></a>
 </span>
 <span class="prev">

  <a href="function.strripos.php"><img src="/images/caret-l.gif" alt="&lt;" width="11" height="7" />strripos</a>
 </span>
 <hr />
 <span class="lastupdated">Last updated: Fri, 23 Jul 2010</span>
 <div class="langchooser">
  &nbsp;
 </div>
</div>

<!--/UdmComment-->


 </div>
 <div class="cleaner">&nbsp;</div>
</div>

<div id="footnav">
   <a href="/source.php?url=/manual/en/function.strrpos.php">show source</a> |
 <a href="/credits.php">credits</a> |
 <a href="/sitemap.php">sitemap</a> |
 <a href="/contact.php">contact</a> |
 <a href="/contact.php#ads">advertising</a> |
 <a href="/mirrors.php">mirror sites</a>

</div>

<div id="pagefooter">
 <div id="copyright">
  <a href="/copyright.php">Copyright &copy; 2001-2010 The PHP Group</a><br />
  All rights reserved.
 </div>

 <div id="thismirror">
  <a href="/mirror.php">This mirror</a> generously provided by:
  <a href="http://www.nexen.net/">nexen.net</a><br />

  Last updated: Wed Jul 28 04:22:24 2010 CEST
 </div>
</div>
<!--[if IE 6]>
<script type="text/javascript"> 
    /*Load jQuery if not already loaded*/ if(typeof jQuery == 'undefined'){ document.write("<script type=\"text/javascript\"   src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js\"></"+"script>"); var __noconflict = true; } 
    var IE6UPDATE_OPTIONS = {
        icons_path: "/ie6update/images/"
    }
</script>
<script type="text/javascript" src="/ie6update/ie6update.js"></script>
<![endif]-->
</body>
</html>
MY_HTML;

 return $x;

}