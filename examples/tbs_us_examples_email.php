<?php

include_once('tbs_class.php');
include_once('class.phpmailer.php'); // the mailer class

// prepare the data
$data = array();
$data[0] = array('email'=>'bob@dom1.com', 'firstname'=>'Bob', 'lastname'=>'Rock');
$data[0]['articles'][] = array('caption'=>'Book - Are you a geek?' , 'qty'=>1 ,'uprice'=>12.5);
$data[0]['articles'][] = array('caption'=>'DVD - The new hope'     , 'qty'=>1 ,'uprice'=>11.0);
$data[0]['articles'][] = array('caption'=>'Music - Love me tender' , 'qty'=>1 ,'uprice'=>0.99);

$data[1] = array('email'=>'evy@dom1.com', 'firstname'=>'Evy', 'lastname'=>'Studette');
$data[1]['articles'][] = array('caption'=>'Drink - Cola'           , 'qty'=>3 ,'uprice'=>0.99);

$data[2] = array('email'=>'babe@dom1.com', 'firstname'=>'Babe', 'lastname'=>'Moonlike');
$data[2]['articles'][] = array('caption'=>'Book - Love is love'    , 'qty'=>1 ,'uprice'=>12.5);
$data[2]['articles'][] = array('caption'=>'Book - Never panic'     , 'qty'=>1 ,'uprice'=>11.0);

$data[3] = array('email'=>'stephan@dom1.com', 'firstname'=>'Stephan', 'lastname'=>'Kimer');
$data[3]['articles'][] = array('caption'=>'DVD - The very last weekend' , 'qty'=>1 ,'uprice'=>12.5);
$data[3]['articles'][] = array('caption'=>'DVD - Frozen in September'     , 'qty'=>1 ,'uprice'=>11.0);
$data[3]['articles'][] = array('caption'=>'Music - Obladi Oblada' , 'qty'=>1 ,'uprice'=>0.99);
$data[3]['articles'][] = array('caption'=>'Music - Push push' , 'qty'=>1 ,'uprice'=>0.99);

// prepare the body's template
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_email.txt',false);
$tpl_subject = $TBS->TplVars['subject']; // retrieve the subject from the template
$tpl_body = $TBS->Source;

// prepare the mailer
$Mail = new PHPMailer();
$Mail->FromName = 'TBS example';
$Mail->From = 'example@tinybutstrong.com';

// merge and send each email
foreach ($data as $recipiant) {

  // merge the body
	$TBS->Source = $tpl_body;	// initialize TBS with the body template
	$TBS->MergeField('i', $recipiant); // merge the current recipiant
	$TBS->MergeBlock('a', $recipiant['articles']);
	$TBS->Show(TBS_NOTHING); // merge automatic TBS fields
	
	// prepare the email
	$Mail->AddAddress($recipiant['email']);  
	$Mail->Subject = $tpl_subject;
	$Mail->Body = $TBS->Source;
	
	// send the email
	//$Mail->Send(); // canceled because there must be no email sending in the examples, we display the messages instead
	$txt = 'To: '.$recipiant['email']."\r\n".'Subject: '.$tpl_subject."\r\n".$Mail->Body."\r\n\r\n============================================\r\n\r\n";
	$TBS->Source = '<html><head><link href="./tbs_us_examples_styles.css" rel="stylesheet" type="text/css"></head><body><h1>Example of emailing</h1><div id="main-body"><pre>'.$txt.'</pre></div></body></html>';
	$TBS->Show();
	break;
}

?>