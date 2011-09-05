<?php

/*
********************************************************
QuickPDO
This class extends the very PDO objects to add shortcuts like the Zend Adaptater has.
I wish such shorcuts were implemeted in the native PDO class.
------------------------
Version  : 1.0 for PHP 5
Date     : 2011-09-05
Web site : http://www.tinybutstrong.com
Author   : http://www.tinybutstrong.com/onlyyou.html
********************************************************
This library is free software.
You can redistribute and modify it even for commercial usage,
but you must accept and respect the LPGL License version 3.
*/

class QuickPDO extends PDO {

	public $fetch_style = PDO::FETCH_ASSOC;
	
	/* Execute an SQL statement and return the number of affected rows.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 * see http://www.php.net/manual/en/pdostatement.execute.php
	 */
	function execute($sql, $values = array()) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		return $stmt->rowCount();
	}

	/* Return all records corresponding to an SQL query.
	 * Similar to PDOStatement::fetchAll() but directly inlcudes the SQL statement.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 * @param {constant} $fetch_style: see http://www.php.net/manual/en/pdostatement.fetch.php
	 */
	function fetchAll($sql, $values = array(), $fetch_style=null) {
		if (!is_array($values)) $values = array($values);
		if ($fetch_style===null) $fetch_style = $this->fetch_style;
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetchAll($fetch_style);
		return $result;
	}

	/* Return the first record corresponding to an SQL statement.
	 * Similar to PDOStatement::fetch() but directly inlcudes the SQL statement.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 * @param {constant} $fetch_style: see http://www.php.net/manual/en/pdostatement.fetch.php
	 */
	function fetchRow($sql, $values = array(), $fetch_style=null) {
		if (!is_array($values)) $values = array($values);
		if ($fetch_style===null) $fetch_style = $this->fetch_style;
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetch($fetch_style);
		return $result;
	}

	/* Return the first value of the first record corresponding to an SQL statement.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 */
	function fetchOne($sql, $values = array() ) {
		$result = $this->fetchRow($sql, $values, PDO::FETCH_NUM);
		if (is_array($result)) {
			return $result[0];
		} else {
			return $result;
		}
	}

	/* Return all records of an SQL statement as an associative array
	 * where the first column of the query becomes the keys of the array, and
	 * the second column of the query becomes the values of the array.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 */
	function fetchPairs($sql, $values = array() ) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$result[$row[0]] = $row[1];
		}
		return $result;
	}

	/* Return all values of the first colmun of an SQL statement as a indexed array.
	 * @param {string} $sql: the sql statement
	 * @param {array} $values: An array of values with as many elements as there are bound parameters in the SQL statement being executed.
	 */
	function fetchCol($sql, $values = array() ) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		return $result;
	}

}