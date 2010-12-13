<?php

// Example 1
// ---------
$books = array();
$books[] = array('title'=>'Robot Dreams', 'author'=>'Isaac Asimov', 'price'=>7.99, 'stock'=>11);
$books[] = array('title'=>'The Hitch Hiker\'s Guide to the Galaxy', 'author'=>'Douglas Adams', 'price'=>22.17, 'stock'=>5);
$books[] = array('title'=>'A.I: Artificial Intelligence', 'author'=>'', 'price'=>20.23, 'stock'=>1);
$books[] = array('title'=>'The Da Vinci Code', 'author'=>'Dan Brown', 'price'=>23.12, 'stock'=>0);
$books[] = array('title'=>'1634: The Ram Rebellion', 'author'=>'Eric Flint', 'price'=>15.75, 'stock'=>7);
$books[] = array('title'=>'The Possibility of an Island', 'author'=>'Michel Houellebecq', 'price'=>16.47, 'stock'=>10);
$books[] = array('title'=>'Among the Free', 'author'=>'Margaret Peterson Haddix', 'price'=>11.02, 'stock'=>15);
$books[] = array('title'=>'Pushing Ice', 'author'=>'Alastair Reynolds', 'price'=>16.35, 'stock'=>8);
$books[] = array('title'=>'Dragon\'s Fire', 'author'=>'Anne McCaffrey', 'price'=>16.47, 'stock'=>2);
$books[] = array('title'=>'The Last Siege, The Final Truth', 'author'=>'John Ostrander', 'price'=>11.67, 'stock'=>5);

// Example 2
// ---------
$tasks = array();
$tasks[] = array('t_id'=> 1, 'title'=>'Screen Main');
$tasks[] = array('t_id'=> 2, 'title'=>'Screen Search');
$tasks[] = array('t_id'=> 3, 'title'=>'Screen Convert');
$tasks[] = array('t_id'=> 4, 'title'=>'Export Module');
$tasks[] = array('t_id'=> 5, 'title'=>'Import Module');
$tasks[] = array('t_id'=> 6, 'title'=>'Admin Module');
$tasks[] = array('t_id'=> 7, 'title'=>'Archive Module');
$tasks[] = array('t_id'=> 8, 'title'=>'Mac OSX compatibility');
$tasks[] = array('t_id'=> 9, 'title'=>'Debugging');
$tasks[] = array('t_id'=>10, 'title'=>'New queries');

$employees = array();
$employees[] = array('e_id'=>1, 'fname'=>'Boby', 'lname'=>'Green');
$employees[] = array('e_id'=>2, 'fname'=>'Julie', 'lname'=>'Robinet');
$employees[] = array('e_id'=>3, 'fname'=>'Marc', 'lname'=>'Plonckt');
$employees[] = array('e_id'=>4, 'fname'=>'Steeve', 'lname'=>'Mac King');
$employees[] = array('e_id'=>5, 'fname'=>'John', 'lname'=>'Travalto');
$employees[] = array('e_id'=>6, 'fname'=>'Mary', 'lname'=>'Douglas');

$times = array();
$times[] = array('t_id'=>10, 'e_id'=>1, 'hour'=>0.5);
$times[] = array('t_id'=> 2, 'e_id'=>1, 'hour'=>1.0);
$times[] = array('t_id'=> 7, 'e_id'=>1, 'hour'=>2.0);
$times[] = array('t_id'=> 5, 'e_id'=>2, 'hour'=>1.5);
$times[] = array('t_id'=> 8, 'e_id'=>2, 'hour'=>1.5);
$times[] = array('t_id'=> 2, 'e_id'=>2, 'hour'=>2.0);
$times[] = array('t_id'=> 7, 'e_id'=>3, 'hour'=>1.5);
$times[] = array('t_id'=> 8, 'e_id'=>3, 'hour'=>0.5);
$times[] = array('t_id'=> 6, 'e_id'=>3, 'hour'=>1.5);
$times[] = array('t_id'=> 9, 'e_id'=>4, 'hour'=>0.5);
$times[] = array('t_id'=>10, 'e_id'=>4, 'hour'=>2.0);
$times[] = array('t_id'=> 4, 'e_id'=>4, 'hour'=>1.5);
$times[] = array('t_id'=> 6, 'e_id'=>5, 'hour'=>1.0);
$times[] = array('t_id'=> 3, 'e_id'=>5, 'hour'=>1.5);
$times[] = array('t_id'=> 1, 'e_id'=>5, 'hour'=>2.0);
$times[] = array('t_id'=> 1, 'e_id'=>6, 'hour'=>1.0);
$times[] = array('t_id'=> 9, 'e_id'=>6, 'hour'=>1.5);
$times[] = array('t_id'=> 3, 'e_id'=>6, 'hour'=>0.5);
$times[] = array('t_id'=> 5, 'e_id'=>6, 'hour'=>0.5);

// Rearange times data
$times2 = array();
foreach ($times as $rec) {
	$times2[$rec['t_id']][$rec['e_id']] = $rec['hour'];
}

?>