<?php

/*
********************************************************
TbsPDO
This class gives the same shortcuts as the Zend Db Adapater to PDO objects.
------------------------
Version  : 1.0 for PHP 5
Date     : 2011-08-24
Web site : http://www.tinybutstrong.com
Author   : http://www.tinybutstrong.com/onlyyou.html
********************************************************
This library is free software.
You can redistribute and modify it even for commercial usage,
but you must accept and respect the LPGL License version 3.
*/

class PDO_Easy extends PDO {

	public $fetch_style = PDO::FETCH_ASSOC;
	
	/* Execute an SQL statement and return the number of affected rows.
	 * @param {string} $sql
	 * @param {string} $values
	 */
	function execute($sql, $values = array()) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		return $stmt->rowCount();
	}

	/* Return all records corresponding to an SQL query.
	 * Similar to PDOStatement::fetchAll() but inlcude directly the SQL statement.
	 */
	function fetchAll($sql, $values = array(), $fetch_style=null) {
		if (!is_array($values)) $values = array($values);
		if ($fetch_style===null) $fetch_style = $this->fetch_style;
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetchAll($fetch_style);
		return $result;
	}

	/* Return the first record corresponding to an SQL query.
	 * Similar to PDOStatement::fetch() but inlcude directly the SQL statement.
	 */
	function fetchRow($sql, $values, $fetch_style=null) {
		if (!is_array($values)) $values = array($values);
		if ($fetch_style===null) $fetch_style = $this->fetch_style;
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetch($fetch_style);
		return $result;
	}

	/* Return the first value of the first record corresponding to an SQL query.
	 */
	function fetchOne($sql, $values) {
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
	 */
	function fetchPairs($sql, $values) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$result[$row[0]] = $row[1];
		}
		return $result;
	}

	function fetchCol($sql, $values) {
		if (!is_array($values)) $values = array($values);
		$stmt = $this->prepare($sql);
		$stmt->execute($values);
		$result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
		return $result;
	}

}