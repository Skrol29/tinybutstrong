<?php

include_once('../tbs_class.php');
include_once('class.phpmailer.php'); // the mailer class

// Prepare the data
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

// Prepare the body's template
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_email.txt', false); // false stands for "no charset conversion"
$tpl_contents = $TBS->Source;

// Prepare the mailer
$Mail = new PHPMailer();
$Mail->FromName = 'TBS example';
$Mail->From = 'example@tinybutstrong.com';

// Tune this script for the demo, in order to have no email actually sent...
$send_emails = false;
$display_last_as_example = true;
$demo_result = array();

// Merge and send each email
foreach ($data as $recipiant) {

	// Count the articles
	$recipiant['nb_articles'] = 0;
	foreach ($recipiant['articles'] as $article) {
		$recipiant['nb_articles'] += $article['qty'];
	}

    // Merge the contents
	$TBS->Source = $tpl_contents;	// initialize TBS with the body template
	$TBS->MergeField('i', $recipiant); // merge the current recipiant
	$TBS->MergeBlock('a', $recipiant['articles']);
	$TBS->Show(TBS_NOTHING); // merge automatic TBS fields

	// extract the subject part from the template
	$Mail->Subject = $TBS->GetBlockSource('subject', false, false, '');
	
	// prepare the email
	$Mail->AddAddress($recipiant['email']);  
	$Mail->Body = $TBS->Source;
	
	// send the email
	if ($send_emails) {
		$Mail->Send(); // canceled because there must be no email sending in the examples, we display the messages instead
	}
	
	if ($display_last_as_example) {
		$demo_result[] = array(
			'to'      => $recipiant['email'],
			'subject' => $Mail->Subject,
			'body'    => $Mail->Body,
		);
	}

}

// Display the result, only for the demo mode
if ($display_last_as_example) {

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('tbs_us_examples_email.txt');

	$TBS->MergeBlock('demo_result', $demo_result);

	$TBS->Show();
	
}



