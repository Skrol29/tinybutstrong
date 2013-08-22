<?php

/*
********************************************************
Translate Plugin for TinyButStrong
Version 1.0 , on 2013-08-21
********************************************************

XML entity:

<div>Hello[+128+]</div> 
<div>Bonjour</div>

XML attribute:

<div att="Hello[*128*]"></div> 
<div att="Bonjour"></div> 

XML entity with parameters:

<div>Hello {0}[+128|Bob+]</div> 
<div>Bonjour Bob</div>


*/

// Name of the class is a keyword used for Plug-In authentication. So it's better to save it into a constant.
define('TBS_TRANSLATE','clsTbsPluginTranslate');

// Constants for direct commands (direct commands are supported since TBS version 3.6.2)
// Direct command must be a string wich is prefixed by the name of the class followed by a dot (.).
define('TBS_TRANSLATE_SET_OPTIONS','clsTbsPluginTranslate.Options');
define('TBS_TRANSLATE_RUN','clsTbsPluginTranslate.Run');

// Put the name of the class into global variable array $_TBS_AutoInstallPlugIns to have it automatically installed for any new TBS instance.
// Example :
// $GLOBALS['_TBS_AutoInstallPlugIns'][] = TBS_THIS_PLUGIN;

class clsTbsPluginTranslate {

	function OnInstall() {
	
		// Manual installation:
		// $TBS->PlugIn(TBS_INSTALL,TBS_THIS_PLUGIN);
		//  or the first call of:
		// $TBS->PlugIn(TBS_THIS_PLUGIN);
		$this->Version = '1.0'; // Versions of installed plug-ins can be displayed using [var..tbs_info] since TBS 3.2.0
		$this->DirectCommands = array(TBS_TRANSLATE_SET_OPTIONS, TBS_TRANSLATE_RUN); // optional, supported since TBS version 3.7.0. Direct Command's ids must be strings.
	
		$this->Tr = new clsTbsTranslate();
	
		return array('OnCommand');
	}

	function OnCommand($x1, $x2 = false) {
		
		if ($x1 === TBS_TRANSLATE_SET_OPTIONS) {
			$this->Tr->SetOptions($x2);
		} elseif ($x1 === TBS_TRANSLATE_RUN) {
			$this->Tr->Translate($this->TBS->Source);
		}

	}
	
}

/*
Version 1.0	
*/
class clsTbsTranslate {
	
	public $AttBeg = '[*';
	public $AttEnd = '*]';
	public $EntBeg = '[+';
	public $EntEnd = '+]';
	public $Prms = '|';
	public $Fct = false;
	public $Debug = false;
	
	function __construct($Options = null) {

		if ($Options) {
			$this->SetOptions($Options);
		}

	}

	function SetOptions($Options) {
		foreach ($Options as $o => $v) {
			switch ($o) {
			case 'attribute':
				$this->AttBeg = $v[0];
				$this->AttEnd = $v[1];
				break;
			case 'entity':
				$this->EntBeg = $v[0];
				$this->EntEnd = $v[1];
				break;
			case 'param':
				$this->Prms = $v;
				break;
			case 'function':
				$this->Fct = $v;
				break;
			case 'debug':
				$this->Debug = $v;
				break;
			}
		}
	}

	function Translate(&$Txt) {
		$this->_remplace($Txt, $this->AttBeg, $this->AttEnd, '"', '"');
		$this->_remplace($Txt, $this->EntBeg, $this->EntEnd, '>', '<');
	}
	
	function _remplace(&$txt, $tago, $tagc, $delo, $delc) {
	
		$tago_len = strlen($tago);
		$tagc_len = strlen($tagc);
		
		$p = 0;
		while ( ($p=strpos($txt, $tago, $p)) !== false ) {
		
			// Position of the tag
			$p1 = $p;
			$p2 = strpos($txt, $tagc, $p1+$tago_len);
			if ($p2==false) break;

			// String bounds
			$t1 = strrpos( substr($txt,0,$p1) , $delo);
			$t2 = strpos($txt, $delc, $p2);

			if ( ($t1===false) || ($t2===false) ) {
			} else {
			
				$t1++;
				$t_len = $t2-$t1; // Length of the string including the tag, but without delimitors
				$str = substr($txt, $t1, $t_len);
				$p1 = $p1 - $t1;
				$p1z = $p1+$tago_len;
				$p2 = $p2 - $t1;
				$prms = substr($str, $p1z, $p2-$p1z);
				
				// Extact params
				$prms = explode($this->Prms, $prms);
				$key = $prms[0];
				array_shift($prms);
				
				// Take the tag off
				$str = substr_replace($str, '', $p1, $p2+$tagc_len-$p1);
				
				// An empty key means the string is the key
				if ($key == '') $key = trim($str);

				// Translate the string
				if ($this->Debug) {
					$str_tr = '***';
				} elseif ($this->Fct) {
					$args = array($key, $args);
					$str_tr = call_user_func_array($this->Fct, $args);
				} else {
					$str_tr = $str;
				}

				// Replace the string in the contents
				$txt = substr_replace($txt, $str_tr, $t1, $t_len);
				
			}
		
		}
		
	}	
	
}