<?php

include_once('tbs_class.php');

//Connexion to the database
/* Use the example below.
$cnx_id = sqlite_open('mydatabase.dat');
*/
$sql_ok = ( isset($cnx_id) && is_resource($cnx_id) ) ? 1 : 0;
if ($sql_ok==0) $cnx_id = 'clear'; // makes the block to be cleared instead of merged with an SQL query.

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_datamysql.htm');

$TBS->MergeBlock('blk1',$cnx_id,'SELECT * FROM t_tbs_exemples');

$TBS->Show();

?>