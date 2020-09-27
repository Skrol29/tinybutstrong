<?php

// Merging a sub-template using the current TBS instance
global $x;
$x = "This text is displayed by an external script.";
$this->Source = '[onshow.x]';
$this->Show();

/*
// Without parameter 'subtpl' in the main template, this example is equivalent to :
$CurrVal = "This text is displayed by an external script.";
*/

