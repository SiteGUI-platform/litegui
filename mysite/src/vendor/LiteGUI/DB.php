<?php
namespace LiteGUI;
use PDO;

/**
 * A wrapper around PDO to provide query shortcut and Master-Slave Central-Remote Connection
 * 
 * Code largely taken from Doctrine\DBAL
 * 
 * Important for the understanding of this connection should be how and when
 * it picks the slave or master, central or remote.
 *
 * 1. Only [central][master][] connection required, other connections will use [central][master][] connection if not defined
 * 2. Connections to central_master/slave must be explicitly called, default to remote connections
 * 3. Slave if 'read', 'select', fetchAssoc, fetchArray, fetchColumn, fetchAll is used , read is read only
 * 4. Master picked when 'write', 'exec', 'insert', 'delete', 'update', 'prepare' is called.
 * 5. One master/slave connection is randomly picked ONCE during a request.
 *
 * ATTENTION: You can write to the slave with this connection if you use read incorrectly
 *
 *      $conn->read("DELETE FROM table");
 *
 * Be aware that read is a method specifically for READ operations only.
 * 
 * You can manually connect to the master at any time by calling:
 *
 *      $conn->connect('central');
 *      $conn->connect('remote', 'master');
 *      $conn->connect('central', 'master');      
 *
 * @example
 *
 * $config = array(
 *    'central' => array(
 *        'master' => array(
 *            array('user' => 'master1', 'password', 'host' => '', 'dbname' => ''),
 *            array('user' => 'master2', 'password', 'host' => '', 'dbname' => ''),
 *        ),
 *        'slave' => array(
 *            array('user' => 'slave1', 'password', 'host' => '', 'dbname' => ''),
 *            array('user' => 'slave2', 'password', 'host' => '', 'dbname' => ''),
 *        )
 *    ),                   
 *    'remote'  => array(
 *        'master' => array(
 *            array('user' => 'master1', 'password', 'host' => '', 'dbname' => ''),
 *            array('user' => 'master2', 'password', 'host' => '', 'dbname' => ''),
 *        ),
 *        'slave' => array(
 *            array('user' => 'slave1', 'password', 'host' => '', 'dbname' => ''),
 *            array('user' => 'slave2', 'password', 'host' => '', 'dbname' => ''),
 *        )
 *    )
 * );
 */
 
class DB {
	protected $config;
	protected $connections;
	protected $_conn;
    protected $_where = "remote";
    protected $defaultFetchMode = PDO::FETCH_ASSOC;

	public function __construct($config){
		$this->config = $config;
	}

	/**
	 * Add new connection to the connection pool
	 * 
     * @param [type] $where     [description]
	 * @param [type] $name     [description]
	 * @param [type] $user     [description]
	 * @param [type] $password [description]
	 * @param [type] $host     [description]
	 * @param [type] $dbname   [description]
	 */
	public function addConnection($where, $name, $user, $password, $host, $dbname)
	{
		$this->config[$where][$name][] = array('user' => $user, 'password' => $password, 'host' => $host, 'dbname' => $dbname);
		unset($this->connections[$where][$name]); // remove previously defined connection $type (slave = master when slave is not set)
	}	

   	/**
     * Establishes the connection with the database.
     *
     * @return boolean TRUE if the connection was successfully established, FALSE if
     *                 the connection is already open.
     */
   	public function connect($where = null, $name = null)
    {
        $requestedConnectionChange = ($where !== null OR $name !== null);
        // If we have a connection open, and this is not an explicit connection
        // change request, then abort right here, because we are already done.
        // This prevents writes to the slave in case of "keepSlave" option enabled.
        if ($this->_conn && !$requestedConnectionChange) {
            return false;
        }

        if (!empty($where)) {
            $this->_where = $where;
        } else {
            $where = $this->_where;
        } 
        $name = $name ? $name : 'master';

        if (($where !== 'central' && $where !== 'remote') OR ($name !== 'slave' && $name !== 'master')) {
            throw new \Exception("Invalid option to connect(), only [central|remote] [master|slave] allowed.");
        }

        // establish connection, default to central::master which must always be defined 
		if (!empty($this->config[$where][$name])) {
            $this->connections[$where][$name] = $this->_conn = $this->connectTo($where, $name);
        } elseif ($where == 'remote' AND !empty($this->config['remote']['master'])) { //undefined remote slave default to remote master
            $this->connections[$where][$name] = $this->_conn = $this->connectTo('remote', 'master');
        } else {
           	$this->connections[$where][$name] = $this->_conn = $this->connectTo('central', 'master');
        } 
        return true;
    }
   	/**
     * Connects to a specific connection.
     *
     * @param string $name [central_masters, central_slaves, masters, slaves]
     *
     * @return PDO
     */
    protected function connectTo($where, $name)
    {
        if ($this->connections[$where][$name]) { // connection is already established
            return $this->connections[$where][$name];
        } else {
	        $connectionParams = $this->config[$where][$name][array_rand($this->config[$where][$name])];
	        $user = isset($connectionParams['user']) ? $connectionParams['user'] : null;
	        $password = isset($connectionParams['password']) ? $connectionParams['password'] : null;
	        $host = isset($connectionParams['host']) ? $connectionParams['host'] : null;
	        $dbname = isset($connectionParams['dbname']) ? $connectionParams['dbname'] : null;

	        $db = new \PDO("mysql:host=". $host .";dbname=". $dbname .";charset=utf8", $user, $password);
	        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
	        return $db;
	    }    
    }

    /**
     * Sets the fetch mode.
     *
     * @param integer $fetchMode
     *
     * @return void
     */
    public function setFetchMode($fetchMode)
    {
        $this->defaultFetchMode = $fetchMode;
    }

    /**
     * Prepares and executes an SQL query and returns the first row of the result
     * as an associative array.
     *
     * @param string $statement The SQL query.
     * @param array  $config    The query parameters.
     *
     * @return array
     */
    public function fetchAssoc($statement, array $config = array())
    {
        return $this->read($statement, $config)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Prepares and executes an SQL query and returns the first row of the result
     * as a numerically indexed array.
     *
     * @param string $statement The SQL query to be executed.
     * @param array  $config    The prepared statement params.
     *
     * @return array
     */
    public function fetchArray($statement, array $config = array())
    {
        return $this->read($statement, $config)->fetch(PDO::FETCH_NUM);
    }

    /**
     * Prepares and executes an SQL query and returns the value of a single column
     * of the first row of the result.
     *
     * @param string  $statement The SQL query to be executed.
     * @param array   $config    The prepared statement params.
     * @param integer $colnum    The 0-indexed column number to retrieve.
     *
     * @return mixed
     */
    public function fetchColumn($statement, array $config = array(), $colnum = 0)
    {
        return $this->read($statement, $config)->fetchColumn($colnum);
    }
    /**
     * Prepares and executes an SQL query and returns the result as an associative array.
     *
     * @param string $statement    The SQL query.
     * @param array  $config The query parameters.
     * @param array  $types  The query parameter types.
     *
     * @return array
     */
    public function fetchAll($statement, array $config = array(), $types = array())
    {
        return $this->read($statement, $config, $types)->fetchAll();
    }

    /**
     * Executes an SQL SELECT statement on a table.
     *
     * @param string $tableName  The name of the table on which to delete.
     * @param string $fields 	 The comma separated list of fields to select
     * @param array  $identifier The deletion criteria. An associative array containing column-value pairs.
     * @param array  $types      The types of identifiers.
     *
     * @return \PDOStatement The executed statement.
     */ 
    public function select($tableName, $fields, array $identifier = array(), array $types = array())
    {
        $criteria = array();

        foreach (array_keys($identifier) as $columnName) {
            $criteria[] = $columnName . ' = ?';
        }

        if ( ! is_int(key($types))) {
            $types = $this->extractTypeValues($identifier, $types);
        }

        $query = 'SELECT '. $fields .' FROM '. $tableName;
        if (!empty($criteria)){
        	$query .= ' WHERE ' . implode(' AND ', $criteria);
    	}
        return $this->read($query, array_values($identifier), $types);
    }
    /**
     * Executes an, optionally parametrized, SQL query.
     *
     * If the query is parametrized, a prepared statement is used.
     *
     * @param string                                      $query  The SQL query to execute.
     * @param array                                       $config The parameters to bind to the query, if any.
     * @param array                                       $types  The types the previous parameters are in.
     *
     * @return \PDOStatement The executed statement.
     *
     */
    public function read($query, array $config = array(), $types = array())
    {
        $this->connect($this->_where, 'slave');
        if ($config) {
            $stmt = $this->_conn->prepare($query);
            if ($types) {
                $this->_bindTypedValues($stmt, $config, $types);
                $stmt->execute();
            } else {
                $stmt->execute($config);
            }
        } else {
            $stmt = $this->_conn->query($query);
        }
        //echo $query;
        $stmt->setFetchMode($this->defaultFetchMode);
        return $stmt;
    }
    /**
     * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
     * and returns the number of affected rows.
     *
     * This method supports PDO binding types as well as DBAL mapping types.
     *
     * @param string $query  The SQL query.
     * @param array  $config The query parameters.
     * @param array  $types  The parameter types.
     *
     * @return integer The number of affected rows.
     *
     *
     */
    public function write($query, array $config = array(), array $types = array())
    {
        $this->connect($this->_where, 'master');

        if ($config) {
            $stmt = $this->_conn->prepare($query);

            if ($types) {
                $this->_bindTypedValues($stmt, $config, $types);
                $stmt->execute();
            } else {
                $stmt->execute($config);
            }
            $result = $stmt->rowCount();
        } else {
            $result = $this->_conn->exec($query);
        }

        return $result;
    }
    /**
     * Inserts a table row with specified data.
     *
     * @param string $tableName The name of the table to insert data into.
     * @param array  $data      An associative array containing column-value pairs.
     * @param array  $types     Types of the inserted data.
     *
     * @return integer The number of affected rows.
     */
    public function insert($tableName, array $data, array $types = array())
    {
        if ( ! is_int(key($types))) {
            $types = $this->extractTypeValues($data, $types);
        }

        $query = 'INSERT INTO ' . $tableName
               . ' (' . implode(', ', array_keys($data)) . ')'
               . ' VALUES (' . implode(', ', array_fill(0, count($data), '?')) . ')';

        return $this->write($query, array_values($data), $types);
    }
    /**
     * Executes an SQL DELETE statement on a table.
     *
     * @param string $tableName  The name of the table on which to delete.
     * @param array  $identifier The deletion criteria. An associative array containing column-value pairs.
     * @param array  $types      The types of identifiers.
     *
     * @return integer The number of affected rows.
     */ 
    public function delete($tableName, array $identifier, array $types = array())
    {
        $criteria = array();

        foreach (array_keys($identifier) as $columnName) {
            $criteria[] = $columnName . ' = ?';
        }

        if ( ! is_int(key($types))) {
            $types = $this->extractTypeValues($identifier, $types);
        }

        $query = 'DELETE FROM ' . $tableName . ' WHERE ' . implode(' AND ', $criteria);

        return $this->write($query, array_values($identifier), $types);
    }

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * @param string $tableName  The name of the table to update.
     * @param array  $data       An associative array containing column-value pairs.
     * @param array  $identifier The update criteria. An associative array containing column-value pairs.
     * @param array  $types      Types of the merged $data and $identifier arrays in that order.
     *
     * @return integer The number of affected rows.
     */
    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        $set = array();

        foreach ($data as $columnName => $value) {
            $set[] = $columnName . ' = ?';
        }

        if ( ! is_int(key($types))) {
            $types = $this->extractTypeValues(array_merge($data, $identifier), $types);
        }

        $config = array_merge(array_values($data), array_values($identifier));

        $sql  = 'UPDATE ' . $tableName . ' SET ' . implode(', ', $set)
                . ' WHERE ' . implode(' = ? AND ', array_keys($identifier))
                . ' = ?';

        return $this->write($sql, $config, $types);
    }

    /**
     * Prepares an SQL statement.
     *
     * @param string $statement The SQL statement to prepare.
     *
     * @return PDOStatement The prepared statement.
     *     */
    public function prepare($statement)
    {
        $this->connect($this->_where, 'master');
        $stmt = $this->_conn->prepare($statement);
        $stmt->setFetchMode($this->defaultFetchMode);
        return $stmt;
    }


    /**
     * Executes an SQL statement and return the number of affected rows.
     *
     * @param string $statement
     *
     * @return integer The number of affected rows.
     *
     */
    public function exec($statement)
    {
        $this->connect($this->_where, 'master');

        return $this->_conn->exec($statement);
    }
    /**
     * Returns the ID of the last inserted row, or the last value from a sequence object,
     * depending on the underlying driver.
     *
     * Note: This method may not return a meaningful or consistent result across different drivers,
     * because the underlying database may not even support the notion of AUTO_INCREMENT/IDENTITY
     * columns or sequences.
     *
     * @param string|null $seqName Name of the sequence object from which the ID should be returned.
     *
     * @return string A string representation of the last inserted ID.
     */
    public function lastInsertId($seqName = null)
    {
        return $this->_conn->lastInsertId($seqName);
    }
    /**
     * Closes the connection.
     *
     * @return void
     */
    public function close()
    {
        unset($this->_conn);
    }                                        	
    /**
     * Extract ordered type list from two associate key lists of data and types.
     *
     * @param array $data
     * @param array $types
     *
     * @return array
     */
    private function extractTypeValues(array $data, array $types)
    {
        $typeValues = array();

        foreach ($data as $k => $_) {
            $typeValues[] = isset($types[$k])? $types[$k] : \PDO::PARAM_STR;
        }

        return $typeValues;
    }
    /**
     * Binds a set of parameters, some or all of which are typed with a PDO binding type to a given statement.
     *
     * @param PDOStatement $stmt   The statement to bind the values to.
     * @param array        $params The map/list of named/positional parameters.
     * @param array        $types  The parameter types 
     *
     * @return void
     *
     */
    private function _bindTypedValues($stmt, array $params, array $types)
    {
        // Check whether parameters are positional or named. Mixing is not allowed, just like in PDO.
        if (is_int(key($params))) {
            // Positional parameters
            $typeOffset = array_key_exists(0, $types) ? -1 : 0;
            $bindIndex = 1;
            foreach ($params as $value) {
                $typeIndex = $bindIndex + $typeOffset;
                if (isset($types[$typeIndex])) {
                    $type = $types[$typeIndex];  
                    $stmt->bindValue($bindIndex, $value, $type);
                } else {
                    $stmt->bindValue($bindIndex, $value);
                }
                ++$bindIndex;
            }
        } else {
            // Named parameters
            foreach ($params as $name => $value) {
                if (isset($types[$name])) {
                    $type = $types[$name];
                    $stmt->bindValue($name, $value, $type);
                } else {
                    $stmt->bindValue($name, $value);
                }
            }
        }
    }        	
}