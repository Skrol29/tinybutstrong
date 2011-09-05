<?php

include_once('quickpdo.php');

// connection to the database
$pdo = new QuickPDO("mysql:host=localhost;dbname=test", "root", "");

// some criterias
$id = 29;
$id_category = 3;
$name = "Peter";

/* Execute an SQL statement
 */
$nbr = $pdo->execute("UPDATE t_test SET name=".$pdo->quote($name)." WHERE id=".$id);
$nbr = $pdo->execute("UPDATE t_test SET name= ? WHERE id = ?", array($id, $name) );
$nbr = $pdo->execute("UPDATE t_test SET name= :name WHERE id = :id", array('id'=>$id, 'name'=>$name) );


/* Fetch all raws from an SQL query
Result can be like:
array (
  0 => array('id' => '3',  'name' => 'Jimmy', ),
  1 => array('id' => '29', 'name' => 'Peter', ),
  2 => array('id' => '31', 'name' => 'Dona',  ),
)
*/
$data = $pdo->fetchAll("SELECT id, name FROM t_test WHERE id_category=".$id_category);
$data = $pdo->fetchAll("SELECT id, name FROM t_test WHERE id_category = ?", array($id_category) );
$data = $pdo->fetchAll("SELECT id, name FROM t_test WHERE id_category = :id_category", array('id_category'=>$id_category) );

/* Fetch the first row from an SQL query
Result can be like:
array('id' => '3',  'name' => 'Jimmy', )
*/
$row = $pdo->fetchRow("SELECT id, name FROM t_test WHERE id_category=".$id_category);
$row = $pdo->fetchRow("SELECT id, name FROM t_test WHERE id_category = ?", array($id_category) );
$row = $pdo->fetchRow("SELECT id, name FROM t_test WHERE id_category = :id_category", array('id_category'=>$id_category) );

/* Fetch the first value from an SQL query
Result can be like:
'3'
*/
$value = $pdo->fetchOne("SELECT id FROM t_test WHERE id_category=".$id_category);
$value = $pdo->fetchOne("SELECT id FROM t_test WHERE id_category = ?", array($id_category) );
$value = $pdo->fetchOne("SELECT id FROM t_test WHERE id_category = :id_category", array('id_category'=>$id_category) );


/* Fetch all raws from an SQL query
Result can be like:
array (
  3 => 'Jimmy' ,
  29 => 'Peter' ,
  31 => 'Dona' ,
)
*/
$pairs = $pdo->fetchPairs("SELECT id, name FROM t_test WHERE id_category=".$id_category);
$pairs = $pdo->fetchPairs("SELECT id, name FROM t_test WHERE id_category = ?", array($id_category) );
$pairs = $pdo->fetchPairs("SELECT id, name FROM t_test WHERE id_category = :id_category", array('id_category'=>$id_category) );


/* Fetch all raws from an SQL query
Result can be like:
array (
  0 => '3' ,
  1 => '29' ,
  2 => '31' ,
)
*/
$col = $pdo->fetchCol("SELECT id FROM t_test WHERE id_category=".$id_category);
$col = $pdo->fetchCol("SELECT id FROM t_test WHERE id_category = ?", array($id_category) );
$col = $pdo->fetchCol("SELECT id FROM t_test WHERE id_category = :id_category", array('id_category'=>$id_category) );
