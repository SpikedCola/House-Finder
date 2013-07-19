<?php
require_once('Smarty3/Smarty.class.php');
/**
 * Skoba PHP Framework
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> April 28, 2013
 */

// load framework

spl_autoload_register(function($class) {
	if (file_exists(__DIR__.'/classes/'.$class.'.class.php')) {
		require_once(__DIR__.'/classes/'.$class.'.class.php');
	}
});


/**
 * Skoba PHP Framework
 * 
 * Db Class
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> April 28, 2013
 */

class Db {

	/**
	 * Current database handle.
	 * 
	 * @var mysqli
	 */
	protected $handle = null;

	/**
	 * Connected boolean.
	 * 
	 * @var bool
	 */
	protected $connected = false;

	/**
	 * A global cache of all the connections made.
	 * 
	 * @var array 
	 */
	protected static $_CONNECTION_CACHE = [];

	/**
	 * An MD5 of the current connection string, to make finding
	 * the current connection in the cache easier.
	 * @var string 
	 */
	protected $connectionMD5 = null;

	public function __construct() {
		// credential check
		if (!defined('DB_USERNAME')) throw new Exception('DB_USERNAME must be defined before instantiating a Db'); 
		if (!defined('DB_PASSWORD')) throw new Exception('DB_PASSWORD must be defined before instantiating a Db'); 
		if (!defined('DB_SERVER'))   throw new Exception('DB_SERVER must be defined before instantiating a Db'); 
		if (!defined('DB_DATABASE')) throw new Exception('DB_DATABASE must be defined before instantiating a Db'); 

		// sanity check
		if (!extension_loaded('mysqli')) throw new Exception('You must enable the "mysqli" extension to use the Db class');

		// all good, connect
		$this->connect();
	}

	/**
	 * Internal database connect function.
	 */
	protected function connect() {
		if (!$this->connected) {
			// see if the current connection is cached
			$this->connectionMD5 = md5(DB_USERNAME.DB_PASSWORD.DB_SERVER.DB_DATABASE);
			if (isset(self::$_CONNECTION_CACHE[$this->connectionMD5])) {
				// use existing connection
				$this->handle = self::$_CONNECTION_CACHE[$this->connectionMD5];
				// make sure connection is still alive
				if ($this->is_connected = $this->handle->ping()) {
					return;
				}
			}

			// make a new connection
			$this->handle = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
			if ($this->handle->connect_error) {
				throw new Exception('Database connection failed: '.$this->handle->connect_error);
			}

			// cache connection
			self::$_CONNECTION_CACHE[$this->connectionMD5] = $this->handle;
		}
	}

	/**
	 * Looks up the structure of a table
	 * 
	 * @param string $table The table name
	 * @return array An associative array of structure details
	 */
	public function structure($table) {
		$output = array ();
		$q = new Query('SHOW COLUMNS FROM ' . $table);
		$columns = $this->getAll($q);
		foreach ($columns as $row) {
			$row['Size'] = null;
			$row['AutoIncrement'] = false;
			$row['Unique'] = false;
			$row['MySQLType'] = $row['Type'];
			// split off the (size) part of the definition
			if (preg_match('/^([a-z]+)(\(([0-9]+)\))?\s*(signed|unsigned)?$/i', $row['Type'], $temp)) {
				$row['Type'] = $temp[1];
				if (isset ($temp[3])) {
					$row['Size'] = (int) $temp[3];
				}
				if (isset ($temp[4]) && $temp[4] == 'unsigned') {
					$row['Signed'] = false;
				}
				else {
					$row['Signed'] = true;
				}
			}
			// mysql has sizes for it's special predefined types
			switch ($row['Type']) {
				case 'bit' :
					$size = 1;
					break;
				case 'tinyint' :
					$size = 8;
					break;
				case 'smallint' :
					$size = 16;
					break;
				case 'mediumint' :
					$size = 24;
					break;
				case 'int' :
					$size = 32;
					break;
				case 'bigint' :
					$size = 64;
					break;
				case 'tinyblob' :
					$size = 255;
					break;
				case 'blob' :
					$size = 65535;
					break;
				case 'mediumblob' :
					$size = 16777215;
					break;
				case 'longblob' :
					$size = 4294967295;
					break;
				case 'tinytext' :
					$size = 255;
					break;
				case 'text' :
					$size = 65535;
					break;
				case 'mediumtext' :
					$size = 16777215;
					break;
				case 'longtext' :
					$size = 4294967295;
					break;
				default:
					$size = $row['Size'];
			}
			$row['Size'] = $size;

			// combine mysql's many different types into a few common ones
			switch ($row['Type']) {
				case 'bit' :
				case 'tinyint' :
				case 'smallint' :
				case 'mediumint' :
				case 'int' :
				case 'bigint' :
					$row['Type'] = 'integer';
					break;
				case 'binary' :
				case 'varbinary' :
				case 'tinyblob' :
				case 'blob' :
				case 'mediumblob' :
				case 'longblob' :
					$row['Type'] = 'binary';
					break;
				case 'tinytext' :
				case 'text' :
				case 'char' :
				case 'varchar' :
				case 'mediumtext' :
				case 'longtext' :
					$row['Type'] = 'string';
					break;
			}

			// for int type get min and max numbers allowed
			if ($row['Type'] == 'integer') {
				if (function_exists('bcpow')) {
					$range = bcpow(2, $row['Size']);
					if ($row['Signed'] && $row['Size'] > 1) {
						$row['Minimum'] = '-' . bcdiv($range, 2);
						$row['Maximum'] = bcsub(bcdiv($range, 2), 1);
					} else {
						$row['Minimum'] = '0';
						$row['Maximum'] = bcsub($range, 1);
					}
				}
				else {
					if ($row['Signed'] && $row['Size'] > 1) {
						$row['Maximum'] = 2147483647;
						$row['Minimum'] = 0;
					} else {
						$row['Maximum'] = 2147483647;
						$row['Minimum'] = -2147483648;
					}
				}
			}

			if ($row['Null'] === 'NO') {
				$row['Null'] = false;
			}
			else {
				$row['Null'] = true;
			}

			$row['PrimaryKey'] = false;
			$row['Unique'] = false;
			if ($row['Key'] == 'PRI') {
				$row['PrimaryKey'] = true;
				$row['Unique'] = true;
			}
			elseif ($row['Key'] == 'UNI') {
				$row['Unique'] = true;
			}

			if (empty($row['Key'])) {
				$row['Key'] = null;
			}

			if ($row['Extra'] === 'auto_increment') {
				$row['AutoIncrement'] = true;
				$row['Extra'] = null;
			} 
			else {
				$row['AutoIncrement'] = false;
			}

			if (empty($row['Extra'])) {
				$row['Extra'] = null;
			}

			if ($row['Default'] == 'CURRENT_TIMESTAMP') {
				$row['Default'] .= '()';
			}

			// for int type get min and max numbers allowed
			if (preg_match('/^(float|double|decimal)\(([0-9]+),([0-9]+)\)\s*(signed|unsigned)?$/i', $row['Type'], $temp)) {
				$row['Type'] = 'float';
				$row['Digits'] = (int) $temp[2];
				$row['Precision'] = (int) $temp[3];
				if (isset ($temp[4]) && $temp[4] == 'unsigned') {
					$row['Signed'] = false;
				}
				else {
					$row['Signed'] = true;
				}
				$whole = $row['Digits'] - $row['Precision'];
				$max = '';
				for ($t = 0; $t < $whole; $t++) {
					$max .= '9';
				}
				$max .= '.';
				for ($t = 0; $t < $row['Precision']; $t++) {
					$max .= '9';
				}
				$row['Maximum'] = $max;
				if ($row['Signed']) {
					$row['Minimum'] = '-' . $max;
				}
				else {
					$row['Minimum'] = '0';
				}
			}

			// parse out enum types
			if (preg_match('/^enum\((.*)\)$/i', $row['Type'], $temp)) {
				$row['Type'] = 'enum';
				$row['Enum'] = [];
				$enums = explode(',', $temp[1]);
				foreach ($enums as $enum) {
					$enum = substr($enum, 1, strlen($enum) - 2);
					$enum = stripslashes($enum);
					$row['Enum'][] = $enum;
				}
			}

			// parse out set types
			if (preg_match('/^set\((.*)\)$/i', $row['Type'], $temp)) {
				$row['Type'] = 'set';
				$row['Set'] = [];
				$enums = explode(',', $temp[1]);
				foreach ($enums as $enum) {
					$enum = substr($enum, 1, strlen($enum) - 2);
					$enum = stripslashes($enum);
					$row['Set'][] = $enum;
				}
			}

			$output[$row['Field']] = [];
			foreach ($row as $key => $val) {
				$key = strtolower($key);
				$output[$row['Field']][$key] = $val;
			}
		}

		return $output;

	}

	public static function escape($value) {
		switch (gettype($value)) {
			case 'boolean':
				if ($value === true) {
					$value = '1';
				}
				else {
					$value = '0';
				}
				break;
			case 'integer':
			case 'double':
				$value = strval($value);
				break;
			case 'string':
				if ($value === '') {
					$value = "''";
					break;
				}
				if (!count(self::$_CONNECTION_CACHE)) {
					self::$_CONNECTION_CACHE[] = new Db();
				}
				$dbh = reset(self::$_CONNECTION_CACHE); // grab first connection
				$value = "'".$dbh->real_escape_string($value)."'";
				break;
			case 'NULL':
			case 'array':
			case 'object':
			case 'resource':
			case 'user function':
			case 'unknown type':
			default:
				$value = 'NULL';
				break;
		}
		return $value;
	}

	/**
	 * Frees and closes a mysqli result.
	 * 
	 * @param mysqli_result $res The mysqli result to clear
	 */
	protected function clear(mysqli_result $res) {
		$res->free_result();
	}

	/**
	 * Internal method for running a raw query
	 * 
	 * @param string $query SQL query to execute
	 */
	protected function query($query) {
		$result = $this->handle->query((string)$query);

		if ($result === false) {
			throw new Exception('Query error: '.$this->handle->error);
		}

		return $result;
	}

	/**
	 * Gets the first possible field specified by the query.
	 * 
	 * @param Query $q
	 * @return mixed The first possible field in the query,
	 * or false if no row is found.
	 */
	public function getOne(Query $q) {
		// save old limit
		$oldLimit = $q->getLimit();

		// limit to one row
		$q->setLimit(1);

		// get result
		$res = $this->query($q->getSelect());

		// put original limit back
		$q->setLimit($oldLimit[0], $oldLimit[1]);

		if ($res->num_rows == 0) {
			return false;
		}

		$row = $res->fetch_row();

		$this->clear($res);

		return $row[0];
	}

	/**
	 * Gets a row of data from a query.
	 * 
	 * @param Query $q
	 * @return mixed An array of row data, or false
	 * if no row is found.
	 */
	public function getRow(Query $q) {
		// save old limit
		$oldLimit = $q->getLimit();

		// limit to one row
		$q->setLimit(1);

		// get result
		$res = $this->query($q->getSelect());

		// put original limit back
		$q->setLimit($oldLimit[0], $oldLimit[1]);

		if ($res->num_rows == 0) {
			return false;
		}

		$row = $res->fetch_assoc();

		$this->clear($res);

		return $row;
	}

	/**
	 * Gets a column of data from a query.
	 * 
	 * @param Query $q
	 * @param string $column
	 * @return array An array of values, or an empty array
	 * if  there are no values to return.
	 */
	public function getCol(Query $q, $column) {
		$rows = [];

		// get result
		$res = $this->query($q->getSelect());

		if ($res->num_rows > 0) {
			// can either grab the first column in the row,
			// or look up our columns, grab the first one,
			// and do it by assoc. either one *could* be 
			// wrong (multiple columns), so im going with 
			// the easier one
			while ($row = $res->fetch_assoc()) {
				if (!array_key_exists($column, $row)) {
					throw new Exception($column.' does not exist in return data set');
				}
				$rows[] = $row[$column];
			}
		}

		$this->clear($res);

		return $rows;
	}

	/**
	 * Gets all the rows of data from a query.
	 * 
	 * @param Query $q
	 * @return array An array of rows, or an empty array 
	 * if there are no rows to return.
	 */
	public function getAll(Query $q) {
		$rows = [];

		// get result
		$res = $this->query($q->getSelect());

		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				$rows[] = $row;
			}
		}

		$this->clear($res);

		return $rows;
	}

	/**
	 * Performs a COUNT(*) on a query and returns the number
	 * of results.
	 * 
	 * @param Query $q
	 * @return int Number of rows in the query result.
	 */
	public function getCount(Query $q) {
		// save old columns
		$oldColumns = $q->getColumns();

		// set new count column
		$q->setColumn('COUNT(*)');

		if (!$count = $this->getOne($q)) {
			$count = 0;
		}

		// restore old columns
		$q->setColumn($oldColumns);

		return $count;
	}

	/**
	 * Gets all the rows of data from a query and returns an
	 * associative array keyed by $column, and either an array of
	 * values if $secondCol is null, or a single value if 
	 * $secondCol is specified
	 * 
	 * @param Query $q
	 * @param string $column
	 * @param string $secondCol
	 * @return array Associative array of $column => (array of values, or a single value)
	 */
	public function getAssoc(Query $q, $column, $secondCol = null) {
		$rows = [];

		// get result
		$res = $this->query($q->getSelect());

		if ($res->num_rows > 0) {
			while ($row = $res->fetch_assoc()) {
				if (!array_key_exists($column, $row)) {
					throw new Exception('Column '.$column.' does not exist in returned data set');
				}
				if ($secondCol !== null && !array_key_exists($secondCol, $row)) {
					throw new Exception('Column '.$secondCol.' does not exist in returned data set');
				}
				// save whole row
				if ($secondCol === null) {
					$data = $row;
				}
				// save only this column
				else {
					$data = $row[$secondCol];
				}
				$rows[$row[$column]] = $data;
			}
		}

		$this->clear($res);

		return $rows;
	}

	/**
	 * Performs an INSERT on the given query.
	 * 
	 * @param Query $q
	 * @param bool $ignore
	 * @returns int ID of inserted row
	 */
	public function insert(Query $q, $ignore = false) {
		$this->query($q->getInsert($ignore));
		return $this->handle->insert_id;
	}

	/**
	 * Performs an UPDATE on the given query.
	 * 
	 * @param Query $q
	 */
	public function update(Query $q) {
		$this->query($q->getUpdate());
	}

	/**
	 * Performs a DELETE on the given query.
	 * 
	 * @param Query $q
	 */
	public function delete(Query $q) {
		$this->query($q->getDelete());
	}

	/**
	 * Begins a transaction.
	 */
	public function beginTransaction() {
		$this->query('START TRANSACTION');
	}

	/**
	 * Commits a transaction.
	 */
	public function commit() {
		$this->query('COMMIT');
	}

	/**
	 * Rolls back a transaction.
	 */
	public function rollback() {
		$this->query('ROLLBACK');
	}
}
/**
 * Skoba PHP Framework
 * 
 * DbTable Class
 * 
 * An abstract representation of a database table.
 * Exposes all columns as properties of the object, along
 * with the ability to create __set_(xxx) and __get_(xxx)
 * functions to extend the way certain properties work.
 * 
 * Also provides beforeSave, afterSave, beforeCreate, 
 * afterCreate, beforeLoad and afterLoad methods for data 
 * validation and whatever else you can think of.
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> May 14, 2013
 */
/**
 * Skoba PHP Framework
 * 
 * DbTable Class
 * 
 * An abstract representation of a database table.
 * Exposes all columns as properties of the object, along
 * with the ability to create __set_(xxx) and __get_(xxx)
 * functions to extend the way certain properties work.
 * 
 * Also provides beforeSave, afterSave, beforeCreate, 
 * afterCreate, beforeLoad and afterLoad methods for data 
 * validation and whatever else you can think of.
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> May 14, 2013
 */

abstract class DbTable {
	protected $tableName = null;

	protected static $db;

	protected $tableInfo = array();

	protected $data = array();

	protected $originalData = array();

	protected $loaded = false;

	protected $id = null;

	protected $primaryKey = null;

	/**
	 * Called before serializing an object
	 */
	public function __sleep() {
		unset(self::$db);
		return array(
		    'data',
		    'id',
		    'loaded',
		    'originalData',
		    'primaryKey',
		    'tableInfo',
		    'tableName'
		);
	}

	/**
	 * Called after unserializing an object
	 */
	public function __wakeup() {
		self::$db = new Db();
	}

	/**
	 * Accepts an id to load a record, or null to create
	 * a new record
	 * 
	 * @param int $id
	 * @throws Exception
	 */
	public function __construct($id = null) {
		if (!self::$db){
			self::$db = new Db();
		}
		if (!$this->tableName) {
			throw new Exception('You must declare a $tableName override in your class before using DbTable');
		}
		$this->tableInfo = $this->db->structure($this->tableName);
		$primaryKeys = 0;
		foreach ($this->tableInfo as $t){
			if($t['key'] == 'PRI'){
				$this->primaryKey = $t['field']; 
				$primaryKeys++;
			}
		}
		if ($primaryKeys > 1) {
			throw new Exception('DbTable is designed for classes with a single primary key');
		}
		$this->id = $id;
	}

	/**
	 * Creates a new row in the database
	 * 
	 * @return int ID of created row
	 * @throws Exception
	 */
	public function create() {
		$this->db->beginTransaction();
		try {
			if (method_exists($this, 'beforeCreate')) {
				$this->beforeCreate();
			}
			if ($this->id) {
				throw new Exception($this->primaryKey.' is already set and may not be re-created.');
			}

			$q = new Query();
			$q->addTable($this->tableName);
			if (empty($this->data)) {
				throw new Exception('Data array is empty.');
			}
			foreach ($this->data as $column => &$value) {
				$value = self::cast($value, $this->tableInfo[$column]);
			}
			unset($value); // clean up reference
			$q->addFields($this->data);
			if ($id = $this->db->insert($q)) {
				// only overwrite id if we get something back from insert
				$this->id = $this->data[$this->primaryKey] = $id;
			}

			if (method_exists($this, 'afterCreate')) {
				$this->afterCreate();
			}

			$this->db->commit();
		} 
		catch(Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$this->loaded = false;

		return $this->id;
	}

	/**
	 * Saves the current row to the database
	 * 
	 * @throws Exception
	 */
	public function save() {
		if (!$this->id) {
			return $this->create();
		}
		$this->db->beginTransaction();
		try {
			if (method_exists($this, 'beforeSave')) {
				$this->beforeSave();
			}

			foreach ($this->data as $column => &$value) {
				$value = self::cast($value, $this->tableInfo[$column]);
			}
			unset($value); // clean up reference
			// see if anything has changed
			if (array_diff_assoc($this->data, $this->originalData)) {
				unset($this->data[$this->primaryKey]);
				$q = new Query();
				$q->addTable($this->tableName);
				$q->addWhere($this->primaryKey, $this->id);
				$q->addFields($this->data);
				$this->db->update($q);
			} 

			if (method_exists($this, 'afterSave')) {
				$this->afterSave();
			}

			$this->db->commit();
		} 
		catch(Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$this->data = array();
		$this->originalData = array();
		$this->loaded = false;
	}

	/**
	 * Clears any changes to the current row
	 */
	public final function reload() {
		if ($this->loaded) {
			$this->data = $this->originalData;
			if (method_exists($this, 'afterLoad')) {
				$this->afterLoad();
			}
		} 
		else {
			$this->load();
		}
	}

	/**
	 * Load the database row into the data array
	 */
	public function load($id = null){
		if ($this->loaded) {
			return;
		}

		$this->loaded = true;

		if ($id) {
			$this->id = $id;
		}

		if ($this->id){
			$this->data = array();
			$q = new Query();
			$q->addTable($this->tableName);
			$q->addWhere($this->primaryKey, $this->id);
			if ($data = $this->db->getRow($q)) {
				foreach ($data as $column => $value) {
					if (!array_key_exists($column, $this->data)) {
						$this->data[$column] = self::cast($value, $this->tableInfo[$column]);
					}
				}
			} 
			else {
				throw new Exception('An id was specified but a corresponding database row does not exist in '.$this->tableName.' for '.$this->id);
			}
		} 
		else {
			foreach ($this->tableInfo as $key => $value) {
				if (!array_key_exists($key, $this->data)) {
					$this->data[$key] = isset($this->tableInfo[$key]['default']) ? $this->tableInfo[$key]['default'] : null;
				}
			}
		}

		$this->originalData = $this->data;

		if (method_exists($this, 'afterLoad')) {
			$this->afterLoad();
		}
	}

	/**
	 * Casts a database value to the type given by the column
	 * definition
	 * 
	 * @param mixed $value
	 * @param array $validation
	 * @return mixed
	 */
	protected static function cast($value, array $validation) {
		if ($value !== null || $validation['null'] === false) {
			switch ($validation['type']) {
				case 'bit':
				case 'integer':
					return intval($value);
				case 'string':
					return strval($value);
				case 'float':
					return floatval($value);
			}
		}
		return $value;
	}

	/**
	 * Magic "getter" to convert $object->property
	 * into a handy __get_property() function
	 * 
	 * @property mixed $var Property to return
	 * @return mixed
	 */
	public function __get($var) {
		switch ($var) {
			// netbeans doesnt self::db->insert() syntax
			// so this will let us do $this->db even with
			// the static db
			case 'db':
				return self::$db;
			case 'id':
				return $this->id;
			case 'primaryKey':
				return $this->primaryKey;
			case 'loaded':
				return $this->loaded;
		}
		if (!$this->loaded) {
			$this->load();
		}
		switch ($var) {
			case 'data':
				return $this->data;
			case 'originalData':
				return $this->originalData;
			default:
				$value = null;
				$matches = [];
				$getter = '_get_'.$var;
				// first try for data/original values
				if (array_key_exists($var, $this->tableInfo)) {
					if (array_key_exists($var, $this->data)) {
						$value = $this->data[$var];
					}
				} 
				elseif(preg_match('/^original_(.*)/',$var,$matches)) {
					$originalVar = $matches[1];
					if(array_key_exists($originalVar, $this->originalData)) {
						$value = $this->originalData[$originalVar];
					}
				}
				// now check for getter functions
				if (method_exists($this, $getter)) {
					$value = $this->$getter($value);
				}
				elseif (!array_key_exists($var, $this->tableInfo) && (!isset($originalVar) || !array_key_exists($originalVar, $this->tableInfo))) {
					throw new Exception($var.' does not exist in '.$this->tableName);
				}
				return $value;
		}
	}

	/**
	 * Magic "setter" to convert eg. $object->height = 12
	 * into a handy __set_height($value[=12]) function
	 * 
	 * @property mixed $var Property to set
	 * @property mixed $value Value to set
	 */
	public function __set($var, $value) {
		if(!$this->loaded) {
			$this->load();
		}
		switch ($var) {
			case 'id':
			case $this->primaryKey:
				throw new Exception('You cannot change '.$this->primaryKey.' once set');
			default:
				$setter = '_set_'.$var;
				if (method_exists($this, $setter)) {
					$value = $this->$setter($value);
				}
				elseif (!in_array($var, array_keys($this->tableInfo))) {
					throw new Exception($var.' does not exist in '.$this->tableName);
				}
				if (array_key_exists($var, $this->tableInfo)) {
					self::validate($var, $value, $this->tableInfo[$var]);
					$this->data[$var] = self::cast($value, $this->tableInfo[$var]);
				}
				return $value;
		}
	}

	/**
	 * Validate a value against database column constraints
	 * 
	 * @param string $column
	 * @param mixed $value
	 * @param array $validation
	 * @throws Exception
	 */
	protected static function validate($column, $value, array $validation = array()) {
		if ($value === null) {
			if ($validation['null'] === false) {
				throw new Exception($column.' is a required field');
			}
		}
		elseif ($validation['default'] !== null) {
			switch ($validation['type']) {
				case 'integer':
					$min = intval($validation['minimum']);
					$max = intval($validation['maximum']);
					try {
						$valid = ValidateThrow($value, 'int', $min, $max);
					} 
					catch (Exception $e) {
						throw new Exception($column.' : '.$value.' is not a valid integer between '.$min.' and '.$max);
					}
					break;
				case 'string':
					$min = 0;
					$max = $validation['size'];
					try {
						$valid = ValidateThrow($value, 'str', $min, $max);
					} 
					catch (Exception $e) {
						throw new Exception($column.' : '.$value.' is not a valid string between '.$min.' and '.$max.' characters');
					}
					break;
				case 'float':
					$min = floatval($validation['minimum']);
					$max = floatval($validation['maximum']);
					try {
						$valid = ValidateThrow($value, 'float', $min, $max);
					} 
					catch (Exception $e) {
						throw new Exception($column.' : '.$value.' is not a valid decimal number between '.$min.' and '.$max);
					}
					break;
				case 'enum':
					try {
						$valid = ValidateThrow($value, 'string', $validation['enum']);
					} 
					catch (Exception $e) {
						throw new Exception($column.' : '. $value.' is not one of: '.join(', ',$validation['enum']));
					}
					break;
				case 'set':
					$values = explode(',', $value);
					foreach ($values as $v) {
						if (!in_array($v, $validation['set'])) {
							throw new Exception($column.': '.$value.' is not one of: '.implode(', ', $validation['set']));
						}
					}
					break;
				case 'date':
					if ($value && Validate($value, 'str', '/^[12][0-9]{3}-([1][012]|[0][0-9])-([3][0-9]|[012][0-9])$/') === null) {
						throw new Exception($column.': '.$value.' should be a date in the format: YYYY-MM-DD.');
					}
					break;
				case 'timestamp':
					if ($value && strtotime($value) === false) {
						throw new Exception($column.': '.$value.' should be a valid date/time');
					}
					break;
				default:
					throw new Exception($column.': '.$validation['type'].' validation does not exist');
			}
		}
	}
}
/**
 * Skoba PHP Framework
 * 
 * Error Functions
 * 
 * Was a class, but a function to throw errors is really
 * no better than just throwing the exception itself.
 * 
 * This also has the benefit of catching any unexpected/
 * unhandled exceptions.
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> 2013-06-019
 */
set_exception_handler(function($ex) {
	if (PROJECT_STATUS == 'development') {
		throw new Exception('Exception!', 0, $ex); // this preserves the call stack
	}
	else {
		// @todo: sqs and report this to an error server of some sort
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 300'); // 300 seconds
		die;
	}
});
/**
 * Skoba PHP Framework
 * 
 * HTTP Class
 * 
 * Provides GET/POST/etc functionality
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> May 9, 2013
 */
class Http {
	/**
	 * GET a url
	 * 
	 * @param string $url url to get
	 * @return array containing response, info and error
	 */
	function get($url) {
		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11');

		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);

		curl_close($ch);

		return array(
		    'response' => $response,
		    'info' => $info,
		    'error' => $error
		);
	}
}

/**
 * Skoba PHP Framework
 * 
 * Query Class
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> April 28, 2013
 */

class Query {

	/**
	 * An associative array of 'table_name' => 'table_name' 
	 * (to maintain a unique array).
	 * 
	 * @var array
	 */
	protected $tables = [];

	/**
	 * An associative array of 'field name' => 'array (operator/value/field)'.
	 * 
	 * @var array 
	 */
	protected $wheres = [];

	/**
	 * An array of 'raw where statements'.
	 * 
	 * @var array 
	 */
	protected $rawWheres = [];

	/**
	 * An array of 'raw fields'.
	 * 
	 * @var array 
	 */
	protected $rawFields = [];

	/**
	 * An associative array of 'field name' => 'array (operator/value/field)'.
	 * 
	 * @var array 
	 */
	protected $havings = [];

	/**
	 * An associative array of 'column name' => 'column name' 
	 * for SELECT queries.
	 * 
	 * @var array
	 */
	protected $columns = [];

	/**
	 * An associative array of 'field name' => 'field name' 
	 * for INSERT/UPDATE queries.
	 * 
	 * @var array
	 */
	protected $fields = [];

	/**
	 * An associative array of 'table name' => 'on query'.
	 * 
	 * @var array
	 */
	protected $innerJoins = [];

	/**
	 * An associative array of 'table name' => 'on query'.
	 * 
	 * @var array
	 */
	protected $leftJoins = [];

	/**
	 * An associative array of 'column name' => 'asc/desc'.
	 * 
	 * @var array
	 */
	protected $orderBys = [];

	/**
	 * The LIMIT (max number of rows) for this query
	 * 
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * The OFFSET of the LIMIT portion for this query
	 * 
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * An array of valid operators for WHERE clauses.
	 * 
	 * @var array
	 */
	protected $validOperators = ['=', '!=', 'IN', 'NOT IN', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];

	/**
	 * A raw SQL statement to execute
	 * 
	 * @param string $sql
	 */	
	protected $sql = null;

	public function __construct($sql = null) {
		$this->sql = $sql;
	}

	/**
	 * Adds a single table or an array of tables to the query.
	 * 
	 * @param string $table
	 */
	public function addTable($table) {
		$this->tables[$table] = $table;
	}

	public function setTable($table) {
		$this->tables = [];
		$this->addTable($table);
	}

	public function getTables() {
		return $this->tables;
	}

	/**
	 * Adds a WHERE clause to a query.
	 * 
	 * @param string $field
	 * @param mixed $value
	 */
	public function addWhere($field, $value = null, $operator = null) {
		if ($operator !== null && !in_array($operator, $this->validOperators)) {
			Error::throwError('Invalid operator specified: '.$operator);
		}

		if (is_array($field)) {
			// assume we're passing an assoc array of $field => $value pairs
			foreach ($field as $key => $val) {
				$this->addWhere($key, $val);
			}
		}
		// dont want to accidentally trigger this if we're doing, say, $q->addWhere('field', null);
		elseif ($field && $value === null && $operator === null && func_num_args() == 1) {
			$this->rawWheres[] = $field;
		}
		else {
			if ($operator === null) {
				if (is_array($value)) {
					// assume operator is 'IN' if not specified and 
					// we have an array of values
					$operator = 'IN'; 
				}	
				else {
					// assume operator is '=' if not specified
					$operator = '='; 
				}
			}

			$this->wheres[] = array(
			    'field' => $field,
			    'value' => $value,
			    'operator' => $operator
			);
		}
	}

	public function setWhere($field, $value, $operator = null) {
		$this->wheres = [];
		$this->addWhere($field, $value, $operator);
	}

	public function getWheres() {
		return $this->wheres;
	}

	/**
	 * Adds a HAVING clause to a query.
	 * 
	 * @param string $field
	 * @param mixed $value
	 */
	public function addHaving($field, $value, $operator = null) {
		if ($operator !== null && !in_array($operator, $this->validOperators)) {
			Error::throwError('Invalid operator specified: '.$operator);
		}

		if ($operator === null) {
			if (is_array($value)) {
				// assume operator is 'IN' if not specified and 
				// we have an array of values
				$operator = 'IN'; 
			}	
			else {
				// assume operator is '=' if not specified
				$operator = '='; 
			}
		}

		$this->havings[] = array(
		    'field' => $field,
		    'value' => $value,
		    'operator' => $operator
		);
	}

	public function setHaving($field, $value, $operator = null) {
		$this->havings = [];
		$this->addHaving($field, $value, $operator);
	}

	public function getHaving() {
		return $this->havings;
	}

	/**
	 * Adds a column of columns for a SELECT query.
	 * 
	 * @param string $name
	 */
	public function addColumn($name) {
		$this->columns[] = $name;
	}

	/**
	 * Adds an array of columns for a SELECT query.
	 * 
	 * @param array $name
	 */
	public function addColumns(array $name) {
		foreach ($name as $n) {
			$this->columns[] = $n;
		}
	}

	/**
	 * Clears current columns and adds a column/array of
	 * columns for a SELECT query.
	 * 
	 * @param mixed $column
	 */
	public function setColumn($column) {
		$this->columns = [];
		$this->addColumn($column);
	}

	/**
	 * Get an array of the current columns.
	 * 
	 * @return array
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * Adds a field for an UPDATE or INSERT query.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function addField($name, $value = null) {
		if (!$value && func_num_args() == 1) {
			$this->rawFields[] = $name;
		}
		else {
			$this->fields[$name] = $value;
		}
	}

	/**
	 * Adds an array of field name => field value for an UPDATE or INSERT query.
	 * 
	 * @param array $values
	 */
	public function addFields(array $values) {
		foreach ($values as $key => $value) {
			$this->fields[$key] = $value;
		}
	}

	public function setField($name, $value) {
		$this->fields = [];
		$this->addField($name, $value);
	}

	public function getFields() {
		return $this->fields;
	}

	/**
	 * Adds an INNER JOIN to a query.
	 * 
	 * @param type $table
	 * @param type $on
	 */
	public function addInnerJoin($table, $on) {
		$this->innerJoins[$table] = $on;
	}

	public function setInnerJoin($table, $on) {
		$this->innerJoins = [];
		$this->addInnerJoin($table, $on);
	}

	public function getInnerJoins() {
		return $this->innerJoins;
	}

	/**
	 * Adds a LEFT JOIN to a query.
	 * 
	 * @param type $table
	 * @param type $on
	 */
	public function addLeftJoin($table, $on) {
		$this->leftJoins[$table] = $on;
	}

	public function setLeftJoin($table, $on) {
		$this->leftJoins = [];
		$this->addLeftJoin($table, $on);
	}

	public function getLeftJoins() {
		return $this->leftJoins;
	}

	/**
	 * An alias of setLimit (for consistency).
	 * 
	 * @param int $limit
	 * @param int $offset
	 */
	public function addLimit($limit, $offset = null) {
		$this->setLimit($limit, $offset);
	}

	/**
	 * Sets the LIMIT of the query.
	 * 
	 * @param int $limit
	 * @param int $offset
	 */
	public function setLimit($limit, $offset = null) {
		if ((string)(int)$limit !== (string)$limit) Error::throwError('Limit value is not an integer: '.$limit);

		$this->limit = $limit;

		if ($offset === null) {
			$this->offset = 0;
		}
		else {
			$this->offset = $offset;
		}
	}

	/**
	 * Gets the current limit and offset of the query.
	 * 
	 * @return array An array of (0 => limit, 1 => offset)
	 */
	public function getLimit() {
		return array(
		    $this->limit,
		    $this->offset
		);
	}

	/**
	 * Adds a column to the ORDER BY of the query.
	 * 
	 * @param string $column
	 * @param string $direction
	 */
	public function addOrder($column, $direction = 'asc') {
		$direction = strtolower($direction);
		if (!in_array($direction, array('asc', 'desc'))) {
			Error::throwError('Invalid ORDER BY direction: '.$direction);
		}
		$this->orderBys[$column] = $direction;
	}

	/**
	 * Sets the ORDER BY of the query.
	 * 
	 * @param string $column
	 * @param string $direction 'asc' or 'desc'
	 */
	public function setOrder($column, $direction = 'asc') {
		$this->orderBys = [];
		$this->addOrder($column, $direction);
	}

	/**
	 * Gets the current order and offset of the query.
	 * 
	 * @return array An array of 'column name' => 'asc/desc'
	 */
	public function getOrder() {
		return $this->orderBys;
	}

	/**
	 * Builds a SELECT query for the current Query
	 * 
	 * @return string A SELECT query for the current Query
	 */
	public function getSelect() {
		if ($this->sql) {
			return $this->sql;
		}
		$sql[] = 'SELECT';
		$sql[] = $this->buildColumns();
		$sql[] = 'FROM';
		$sql[] = $this->buildTables();
		$sql[] = $this->buildJoins();
		$sql[] = $this->buildWhere();
		$sql[] = $this->buildHaving();
		$sql[] = $this->buildOrderBy();
		$sql[] = $this->buildLimit();
		return trim(implode(' ', $sql));
	}

	/**
	 * Builds an INSERT query for the current Query
	 * 
	 * @param bool Creates an INSERT IGNORE if true
	 * @return string An INSERT query for the current Query
	 */
	public function getInsert($ignore = false) {
		$sql[] = 'INSERT';
		if ($ignore) {
			$sql[] = 'IGNORE';
		}
		$sql[] = 'INTO';
		$sql[] = $this->buildTables();
		$sql[] = '('.$this->buildFieldNames().')';
		$sql[] = 'VALUES';
		$sql[] = '('.$this->buildFieldValues().')';
		return trim(implode(' ', $sql));
	}

	/**
	 * Builds an UPDATE query for the current Query
	 * 
	 * @return string An UPDATE query for the current Query
	 */
	public function getUpdate() {
		$sql[] = 'UPDATE';
		$sql[] = $this->buildTables();
		$sql[] = 'SET';
		$sql[] = $this->buildFieldNameValuePairs();
		$sql[] = $this->buildWhere();
		$sql[] = $this->buildLimit();
		return trim(implode(' ', $sql));
	}

	/**
	 * Builds a DELETE query for the current Query
	 * 
	 * @return string A DELETE query for the current Query
	 */
	public function getDelete() {
		$sql[] = 'DELETE FROM';
		$sql[] = $this->buildTables();
		$sql[] = $this->buildWhere();
		$sql[] = $this->buildLimit();
		return trim(implode(' ', $sql));
	}

	/**
	 * Builds a COUNT(*) query for the current Query
	 * 
	 * @return string A COUNT(*) query for the current Query
	 */
	public function getCount() {
		$sql[] = 'SELECT COUNT(*) FROM';
		$sql[] = $this->buildTables();
		$sql[] = $this->buildWhere();
		$sql[] = $this->buildLimit();
		return trim(implode(' ', $sql));
	}

	protected function buildColumns() {
		if (count($this->columns)) {
			return implode(', ', $this->columns);
		}
		return '*';
	}

	/**
	 * Builds a string of "field name = 'field value'" pairs for 
	 * an UPDATE statement
	 * 
	 * @return string "field name = 'field value'[, ...]"
	 */
	protected function buildFieldNameValuePairs() {
		if (!count($this->fields) && !count($this->rawFields)) {
			Error::throwError('Must specify at least one field for this query');
		}
		$fields = [];
		if ($this->fields) {
			$fieldNames = array_keys($this->fields);
			$fieldValues = array_values($this->fields);
			for ($i = 0; $i < count($this->fields); $i++) {
				$fields[] = $fieldNames[$i].'='.Db::escape($fieldValues[$i]);
			}
		}
		if ($this->rawFields) {
			foreach ($this->rawFields as $f) {
				$fields[] = $f;
			}
		}
		return implode(', ', $fields);
	}

	protected function buildFieldNames() {
		if (!count($this->fields)) {
			Error::throwError('Must specify at least one field for this query');
		}
		return implode(', ', array_keys($this->fields));
	}

	protected function buildFieldValues() {
		if (!count($this->fields)) {
			Error::throwError('Must specify at least one field for this query');
		}
		$fields = array_values($this->fields);
		foreach ($fields as &$field) {
			$field = Db::escape($field);
		}
		unset($field);
		return implode(', ', $fields);
	}

	protected function buildTables() {
		if (!count($this->tables)) {
			Error::throwError('You must specify at least one table');
		}
		$tables = $this->tables;
		if (defined('DB_TABLE_PREFIX')) {
			$tables = array_map(function($table) { 
				return DB_TABLE_PREFIX.$table;
			}, $tables);
		}
		return implode(', ', $tables);
	}

	protected function buildJoins() {
		$sql = [];
		foreach ($this->leftJoins as $table => $on) {
			$sql[] = 'LEFT JOIN '.$table.' ON ('.$on.')';
		}
		foreach ($this->innerJoins as $table => $on) {
			$sql[] = 'INNER JOIN '.$table.' ON ('.$on.')';
		}

		return implode(' ', $sql);
	}

	protected function buildWhere() {
		$sql = [];
		$wheres = count($this->wheres);
		$rawWheres = count($this->rawWheres);
		if ($wheres || $rawWheres) {
			$sql[] = 'WHERE';
			$i = 0;
			foreach ($this->wheres as $where) {
				$i++;
				if (is_array($where['value'])) {
					if ($where['operator'] != 'IN') {
						Error::throwError('Unknown operator used with WHERE + array: '.$where['operator']);
					}
					foreach ($where['value'] as &$value) {
						$value = Db::escape($value);
					}
					unset($value);
					$value = '('.implode(', ', $where['value']).')';
				}
				else {
					if ($where['value'] === null) {
						if ($where['operator'] == '=') {
							$where['operator'] = 'IS';
						}
						elseif ($where['operator'] == '!=') {
							$where['operator'] = 'IS NOT';
						}
					}
					$value = Db::escape($where['value']);
				}
				$sql[] = $where['field'];
				$sql[] = $where['operator'];
				$sql[] = $value;
				if ($i < ($wheres+$rawWheres)) {
					$sql[] = 'AND';
				}
			}
			foreach ($this->rawWheres as $query) {
				$i++;
				$sql[] = $query;
				if ($i < ($wheres+$rawWheres)) {
					$sql[] = 'AND';
				}
			}
		}
		return implode(' ', $sql);
	}

	protected function buildHaving() {
		$sql = [];
		if ($having = count($this->havings)) {
			$sql[] = 'HAVING';
			$i = 0;
			foreach ($this->havings as $having) {
				$i++;
				if (is_array($having['value'])) {
					if ($having['operator'] != 'IN') {
						Error::throwError('Unknown operator used with HAVING + array: '.$having['operator']);
					}
					foreach ($having['value'] as &$value) {
						$value = Db::escape($value);
					}
					unset($value);
					$value = '('.implode(', ', $having['value']).')';
				}
				else {
					if ($having['value'] === null) {
						if ($having['operator'] == '=') {
							$having['operator'] = 'IS';
						}
						elseif ($having['operator'] == '!=') {
							$having['operator'] = 'IS NOT';
						}
					}
					$value = Db::escape($having['value']);
				}
				$sql[] = $having['field'];
				$sql[] = $having['operator'];
				$sql[] = $value;
				if ($i < $having) {
					$sql[] = 'AND';
				}
			}
		}
		return implode(' ', $sql);
	}

	protected function buildLimit() {
		$sql = [];
		if ($this->limit) {
			$sql[] = 'LIMIT';
			if ($this->offset) {
				$sql[] = $this->offset;
				$sql[] = ',';
				$sql[] = $this->limit;
			}
			else {
				$sql[] = $this->limit;
			}
		}
		return implode(' ', $sql);
	}

	protected function buildOrderBy() {
		$sql = [];
		if ($orderBys = count($this->orderBys)) {
			$sql[] = 'ORDER BY';
			foreach ($this->orderBys as $column => $direction) {
				$order[] = $column.' '.strtoupper($direction);
			}
			$sql[] = implode(', ', $order);
		}
		return implode(' ', $sql);
	}		
}
/**
 * Skoba PHP Framework
 * 
 * Template Class
 * 
 * Adds the following modifiers:
 * - {{$var}} - auto html-escaped
 * - {%var} - auto isset() check (can be combined with {{}})
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> April 28, 2013
 * @requires Smarty3 
 */
class Template extends Smarty {
	/**
	 * The page title
	 * @var string 
	 */
	public $title = null;
	/**
	 * A wrapper template
	 * @var string 
	 */
	public $wrapper = null;
	/**
	 * An array of css files to load
	 * @var array 
	 */
	public $css = array();
	/**
	 * An array of js files to load
	 * @var array 
	 */
	public $js = array();

	public function __construct() {
		parent::__construct();

		$this->registerFilter('pre', array($this, 'prefilter_percentIsset'));
		$this->registerFilter('pre', array($this, 'prefilter_doubleCurlies'));

		if (!defined('TEMPLATES_DIR'))   throw new Exception('You must define TEMPLATES_DIR to use the Template class'); 
		if (!defined('TEMPLATES_C_DIR')) throw new Exception('You must define TEMPLATES_C_DIR to use the Template class'); 

		$this->setTemplateDir(TEMPLATES_DIR); 
		$this->setCompileDir(TEMPLATES_C_DIR); 

		// if we're in development mode, always recompile templates
		if (PROJECT_STATUS == 'development') {
			$this->force_compile = true;
			$this->caching = 1;
			$this->compile_check = true;
		}
	}

	/**
	 * Converts {{$var}} into {$var|escape:'html'}
	 * 
	 * @param string $code 
	 */
	protected function prefilter_doubleCurlies($code) {
		return preg_replace('/\{\{([\$%][^\}]+)\}\}/', '{$1|escape:"html"}', $code);
	}

	/**
	 * Converts {%var} into {if isset($var}}{$var}{/if}. Also works
	 * with double curlies: {{%var}} -> {if isset($var)}{{$var}}{/if}
	 * 
	 * @param string $code 
	 */
	protected function prefilter_percentIsset($code) {
		return preg_replace('/(\{{1,2})%([^\}]+)(\}{1,2})/', '{if isset(\$$2)}$1\$$2$3{/if}', $code);
	}

	/**
	 * Fetches a template. Implicitly called by $this->display()
	 * 
	 * @global array $errors
	 * @param string $template Template to display. ".tpl" will be added if omitted
	 * @return string Template contents
	 */
	public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
		global $errors;
		//$this->compile_id = md5($id);

		// auto add .tpl extension if not specified
		if ($template && pathinfo($template, PATHINFO_EXTENSION) != 'tpl') {
			$template .= '.tpl';
		}

		// auto assign errors array
		if (is_array($errors)) {
			$this->assign('errors', $errors);
		}			
		$this->assign('_title', $this->title);
		$this->assign('_css', $this->css);
		$this->assign('_js', $this->js);
		$this->assign('_content', $template);

		if ($this->wrapper) {
			return parent::fetch($this->wrapper, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
		} else {
			return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
		}
	}
}
/**
 * Skoba PHP Framework
 * 
 * Validation functions. Don't really want to make it a static class
 * so this will just be a collection of useful Validation functions
 * 
 * @author Jordan Skoblenick <parkinglotlust@gmail.com> May 4, 2013
 */

function Validate($value, $type = 'str', $test1 = null, $test2 = null) {
	try {
		return ValidateThrow($value, $type, $test1, $test2);
	}
	catch (Exception $ex) {
		return null;
	}
}

function ValidateArrayKey($key, $array, $type = 'str', $test1 = null, $test2 = null) {
	if (!is_array($array)) return null;
	if (!array_key_exists($key, $array)) return null;
	return Validate($array[$key], $type, $test1, $test2);
}

function GET($key, $type = 'str') {
	if (!isset($_GET[$key])) return null;
	return Validate($_GET[$key], $type);
}

function POST($key, $type = 'str') {
	if (!isset($_POST[$key])) return null;
	return Validate($_POST[$key], $type);
}

function SERVER($key, $type = 'str') {
	if (!isset($_SERVER[$key])) return null;
	return Validate($_SERVER[$key], $type);
}

function SESSION($key, $type = 'str') {
	if (!isset($_SESSION[$key])) return null;
	return Validate($_SESSION[$key], $type);
}

function ValidateThrow($value, $type = 'str', $test1 = null, $test2 = null) {
	if (strpos($type, 'array:') === 0 || strpos($type, 'arr:') === 0) {
		$types = explode(":", $type);
		$type = $types[1];
		if (!is_array($value)) {
			$value = array($value);
		}
		foreach ($value as $k => $v) {
			$value[$k] = Validate($v, $type, $test1, $test2);
		}
		return $value;
	}
	switch ($type) {
		case "int":
		case "integer":
			if (!is_numeric($value)) {
				throw new Exception('Integer: value is not numeric');
			}
			if (intval($value) != $value) {
				throw new Exception('Integer: value is a decimal');
			}
			if (is_array($test1) && !in_array($value, $test1)) {
				throw new Exception('Integer: value is not in list of options');
			}
			if (!is_array($test1) && !is_null($test1) && $value < $test1) {
				throw new Exception('Integer: value is less than minimum');
			}
			if (!is_null($test2) && $value > $test2) {
				throw new Exception('Integer: value is greater than maximum');
			}
			return @intval($value);
		case "dec":
		case "decimal":
		case "float":
			if (!is_numeric($value)) {
				throw new Exception('Float: value is not numeric');
			}
			if (floatval($value) != $value) {
				throw new Exception('Float: value is not a float');
			}
			if (is_array($test1) && !in_array($value, $test1)) {
				throw new Exception('Float: value is not in list of options');
			}
			if (!is_array($test1) && !is_null($test1) && $value < $test1) {
				throw new Exception('Float: value is less than minimum');
			}
			if (!is_null($test2) && $value > $test2) {
				throw new Exception('Float: value is greater than maximum');
			}
			return @floatval($value);
		case "str":
		case "string":
			if (is_resource($value) || is_object($value) || is_array($value)) {
				throw new Exception('String: value cannot be converted to a string');
			}
			if (is_bool($value)) {
				$value = ($value ? 'true' : 'false');
			}
			$value = @strval($value);
			if ($value === false) {
				$value = '';
			}
			if (is_array($test1) && !in_array($value, $test1)) {
				throw new Exception('String: value is not in list of options');
			}
			if (is_string($test1) && !preg_match($test1, $value)) {
				throw new Exception('String: value does not match expression');
			}
			if (is_int($test1) && strlen($value) < $test1) {
				throw new Exception('String: value is less than minimum length');
			}
			if (is_int($test2) && strlen($value) > $test2) {
				throw new Exception('String: value is greater than maximum length');
			}
			return $value;
		case "bool":
		case "boolean":
			if (is_bool($value)) {
				return $value;
			} elseif (is_object($value) || is_array($value)) {
				throw new Exception('Boolean: type cannot be converted to a boolean');
			}
			$value2 = strtoupper($value);
			if ($value2 === "1" || $value2 === 1 || $value2 == "ON" || $value2 === "TRUE" || $value2 === "T" || $value2 === "YES" || $value2 === "Y") {
				return true;
			}
			if ($value2 === "0" || $value2 === 0 || $value2 == "OFF" || $value2 === "FALSE" || $value2 === "F" || $value2 === "NO" || $value2 === "N") {
				return false;
			}
			throw new Exception('Boolean: value cannot be converted to a boolean');
		case "email":
			if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
				throw new Exception('Email: value is not a valid email format');
			}
			return $value;
	}
	return($value);
}