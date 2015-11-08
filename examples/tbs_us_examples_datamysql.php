<?php

include_once('tbs_class.php');

//Connexion to the database
/* Use the example below.
$cnx_id = mysql_connect('localhost','user','password');
mysql_select_db('dbname',$cnx_id);
*/
$sql_ok = ( isset($cnx_id) && is_resource($cnx_id) ) ? 1 : 0;
if ($sql_ok==0) $cnx_id = 'clear'; // makes the block to be cleared instead of merged with an SQL query.

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('tbs_us_examples_datamysql.htm');

$TBS->MergeBlock('blk1',$cnx_id,'SELECT * FROM t_tbs_exemples');

$TBS->Show();

?>