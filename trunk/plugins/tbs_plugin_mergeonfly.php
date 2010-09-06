<?php

/*
********************************************************
TinyButStrong Plug-in: Merge On Fly
Version 1.0.5, on 2006-10-26, by Skrol29
********************************************************
*/

define('TBS_ONFLY','tbsMergeOnFly');

class tbsMergeOnFly {

	function OnInstall($PackSize=10) {
		$this->Version = '1.0.5';
		$this->PackSize = $PackSize;
		return array('OnCommand','BeforeMergeBlock','OnMergeSection');
	}

	function OnCommand($PackSize) {
		$this->PackSize = $PackSize;
	}

	function BeforeMergeBlock(&$TplSource,&$BlockBeg,&$BlockEnd,$PrmLst) {
		if ($this->PackSize>0) {
			$Part2 = substr($TplSource,$BlockBeg);
			$this->TBS->Source = substr($TplSource,0,$BlockBeg);
			$this->TBS->Show(TBS_OUTPUT);
			flush();
			$TplSource = $Part2;
			$BlockEnd = $BlockEnd - $BlockBeg;
			$BlockBeg = 0;
			$this->Counter = 0;
		}
	}

	function OnMergeSection(&$Buffer,&$NewPart) {
		if ($this->PackSize>0) {
			$this->Counter++;
			if ($this->Counter>=$this->PackSize) {
				echo $Buffer.$NewPart;
				flush();
				$Buffer = '';
				$NewPart = '';
				$this->Counter = 0;
			}
			$this->PackSize = 0;
		}
	}


}

?>