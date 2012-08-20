<?php
/** 
 * Database Class
 * Handles MySQL Database Interaction
 *
 * Queries can be created individually:
 *   $query = $database->query();
 *   $query->select(...)->limit(...)->execute();
 *
 * See the Query class (/classes/Query.class.php)
 *
 * Or, single-channel queries may be made, with a 1-result buffer
 *   $database->select(...)->limit(...)->execute();
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.1
 */
class Database extends Base
{
  /**
   * The underlying MySQL connection
   * @var resource
   */
  protected $mysql = null;
  
  /**
   * The current result context
   * @var resource
   */
  protected $result = null;
  
  /**
   * The current buffered query
   * @var string
   */
  protected $query = "";
  
  /**
   * The current position
   * @var integer
   */
  public $pointer = 0;
  
  /**
   * The ID of the last inserted row
   * @var integer
   */
  public $insertID = -1;

  /**
   * Legacy
   * The ID of the last inserted row
   * @var integer
   */
  public $insertId = -1;
  
  /**
   * The number of rows affected by the last operation
   * @var integer
   */
  public $affected = 0;
  
  /**
   * An array that contains information about the most recent query result
   * Associative string => integer
   * @var array
   */
  public $info = array();
  
  /**
   * Connect to the MySQL Server
   * The connection is maintained until close() is called on the object.
   * @param string $sqlUser The MySQL username.
   * @param string $sqlPass The MySQL password.
   * @param string $sqlHost The MySQL hostname.
   * @param string $sqlDatabase The MySQL Database name.
   * @param BFClass* $parent A reference to the parent object.
   * @return Database
   */
  public function __construct($sqlUser, $sqlPass, $sqlHost, $sqlDatabase, $parent)
  {
    // Set parent object
    $this->parent = $parent;
  
    // Load the MySQL connection
    $this->mysql = mysql_connect($sqlHost, $sqlUser, $sqlPass);

    // Nullify password
    $sqlPass = null;

    // Check the SQL connection
    if(!$this->mysql)
    {
      $this->parent->log('MySQL', mysql_error());
      throw new Exception('Failed to build MySQL connection.');
      return false;
    }
     
    // Slect the database
    mysql_select_db($sqlDatabase, $this->mysql);
  }
  
  /**
   * Branch a new query object for multi-result use
   * @return Query
   */
  public function query()
  {
    // Provide this objects MySQL connection
    return new Query($this->mysql, $this->parent);
  }
  
  /**
   * Prepare a query for execution
   * NOTE: This method takes unlimited arguments.
   * @param string $query The query text.
   * @return Database|boolean.
   */
  public function prepare($query)
  {
    // Store the query
    $this->query = $query;
    
    // Get arguments
    $arguments = func_get_args();
    
    // Replace the arugmnets in
    for($argIndex = 0; $argIndex < func_num_args(); $argIndex ++)
    {
      // Find argument
      $argValue = func_get_arg($argIndex);
      
      // Replace content
      $this->query = str_replace('{' . $argIndex . '}', $argValue, $this->query);
      $this->query = str_replace('{int:' . $argIndex . '}', intval($argValue), $this->query);
    }
    
    return $this;
  }
  
  /**
   * Shorthand
   * Retrieves a single row from a single table with the given ID
   * Returns boolean false if no row found or invalid table given
   *
   *  NB: No longer resets the current query since version 1.1
   *
   * @param string $table The table name
   * @param integer $id The Row ID
   * @param string $keyName Optionally a key name other than `id` to use.
   * @param boolean $cacheOverride Optionally override the cache, default false.
   * @return object|boolean False on failure.
   */
  public function getRow($table, $ID, $keyName = 'id', $cacheOverride = false)
  {
    // Branch a query
    $query = $this->query();
  
    // Check if the query is for an ID search
    if($keyName == 'id' && !$cacheOverride)
    {
      // Caching is possible - check...
      $cacheAttempt = $this->parent->cache->getRow($table, $ID);
      
      // Success?
      if($cacheAttempt)
      {
        return $cacheAttempt;
      }
    }
    
    // Search for the row
    $query->select('*', $table)
          ->where('`' . $keyName . '` = \'{1}\'', $ID)
          ->limit(1)
          ->execute();
        
    // Check if a row was found
    if($query->count == 1)
    {
      $returnedRow = $query->next();
      
      if(!$cacheOverride)
      {
        // Cache the loaded row
        $this->parent->cache->addRow($table, $ID, $returnedRow);
      }
      
      // Destroy the query object
      $query = null;
      
      return $returnedRow;
    }
    
    // No row
    return false;
  }
  
  /**
   * Prepare a query to insert a row
   * @param string $tableName The name of the table to insert into.
   * @param array $data The data as an associative array
   * @return boolean True on success, False on failure
   */
  public function insert($tableName, $data)
  {
    // Clean table name
		$tableName = $this->san($tableName);
		
		// Build the field names string
		$fieldsSQL = "INSERT INTO {$tableName}(";
		$valuesSQL = " VALUES(";
		
		// First add an ID column as NULL if it isn't provided
		$fieldsSQL .= (array_key_exists('id', $data) ? '' : '`id`, ');
		$valuesSQL .= (array_key_exists('id', $data) ? '' : 'NULL, ');
		
		foreach($data as $field => $value)
		{
			$valuesSQL .= "'" . $this->san($value) . "', ";
			$fieldsSQL .= "`" . $this->san($field) . "`, ";
		}
		
		// Crop off the last , from Fields and Values
		$valuesSQL = substr($valuesSQL, 0, -2);
		$fieldsSQL = substr($fieldsSQL, 0, -2);
		
		//Finish:
		$this->query = $fieldsSQL . ') ' . $valuesSQL . ') ';
		
		// Return a ref. to this object for chainability
		return $this;
  }

  /**
   * Construct a delete statement
   * @param string $tables The tables list
   * @return Database|boolean
   */
  public function delete($tables)
  {
    // Make column & table names safe
    $tables = $this->san($tables);
    
    // 'DELETE .. FROM ..' query stub
    $this->query = "DELETE FROM {$tables} ";
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Construct a select statement
   * @param string $columns The column list
   * @param string $tables The tables list
   * @return Database|boolean
   */
  public function select($columns, $tables)
  {
    // Make column & table names safe
    $tables = $this->san($tables);
    $columns = $this->san($columns);
    
    // 'SELECT .. FROM ..' query stub
    $this->query = "SELECT {$columns} FROM {$tables} ";
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Construct a select distinct statement
   * @param string $columns The column list.  These are marked Distinct.
   * @param string $tables The tables list
   * @return Database|boolean
   */
  public function distinct($columns, $tables)
  {
    // Make column & table names safe
    $tables = $this->san($tables);
    $columns = $this->san($columns);
    
    // 'SELECT DISTINCT .. FROM ..' query stub
    $this->query = "SELECT DISTINCT {$columns} FROM {$tables} ";
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Add text to the query
   * @param string $text The text to add.
   * @return Database|boolean
   */
  public function text($text)
  {
    // Make text safe
    $text = $this->san($text);
    
    // Join text
    $this->query .= stripslashes($text) . ' ';
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Add a grouping method
   * @param string $column The column to group by.
   * @return Database|boolean
   */
  public function group($column)
  {
    // Make column name safe
    $column = $this->san($column);
    
    // GROUP BY .. 
    $this->query .= 'GROUP BY ' . $column . ' ';
    
    // Return a ref. to this object for chainability
    return $this;
  }

  /**
   * Construct an update statement, modifying rows in a single table
   * @param string $table The table to update
   * @param array $data The columns to update with their new values as an associative array.
   * @return Database|boolean
   */
  public function update($table, $data)
  {
    // Make table name safe
    $table = $this->san($table);
  
    // 'UPDATE .. SET' query stub
    $updateQuery = "UPDATE {$table} SET ";
    
    // Add the column/value updates
    foreach($data as $field => $value)
		{
			$updateQuery .= '`' . $this->san($field) . "` = '" . $this->san($value) . "', ";
		}
		
		// Remove comma
		$this->query = substr($updateQuery, 0, -2) . ' ';
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Add a rule to update a given row if an INSERT would cause a key violation
   * Must be applied after Database::select()
   * @param array $data The columns to update with their new values as an associative array.
   * @return Database|boolean
   */
  public function orUpdate($data)
  {
    // Add the extra rule
    $orUpdateQuery = 'ON DUPLICATE KEY UPDATE ';
    
    // Add the column/value updates
    foreach($data as $field => $value)
		{
			$orUpdateQuery .= '`' . $this->san($field) . "` = '" . $this->san($value) . "', ";
		}
		
		// Remove comma
		$this->query .= substr($orUpdateQuery, 0, -2) . ' ';
		
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Add a where condition
   * NB:  This method accepts an unlimited number of arguments.
   * @param mixed $conditions The conditions to apply.
   * @return Database|boolean
   */
  public function where($conditions)
  {
    if(!$this->query)
    {
      return false;
    }
    
    // Were multiple arguments passed?
    if(func_num_args() > 1)
    {
      $conditions = func_get_args();
    }
    
    $conditions = stripslashes($this->san($conditions));
    $this->query .= "WHERE {$conditions} ";
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Build a preprepared query from an IN hash generated by the Database::getInHash method.
   * This method facilitates normalising of M2M relationships. 
   *
   * Here is a sample of the usage:
   *
   *   // The first query:
   *   $db->select('*', 'item_images')
   *      ->where('item_id = {1}', 1)
   *      ->execute();
   *
   *   $inHash = $db->getInHash('image_id');
   *
   *   // Now a second query
   *   $db->select('*', 'images')
   *      ->whereInHash($inHash)
   *      ->execute();
   *
   * @param string $hash The IN hash to query against
   * @param string $keyName Optionally a key other than `id` to use while retrieving via hash.
   * @return Database|boolean
   */
  public function whereInHash($hash, $keyName = 'id')
  {
    if(!$this->query)
    {
      return $this;
    }
    
    // Check for empty hash
    if(!$hash)
    {
      // Negate
      $this->query .= 'WHERE 1=0 ';
      return $this;
    }
  
    $this->query .= 'WHERE `' . $keyName . '` IN (' . $hash . ') ';
  
    // Return a ref. to this object for chainability
    return $this;
  }
   
  /**
   * Gets a hash of IDs for use with the whereInHash method.
   * 
   *  NB: Resets the current query.
   *      The object will be left in the state after the query.
   *
   * @param string $columnName Optionally the column to hash.  Default 'id'.
   * @return string
   */
  public function getInHash($columnName = 'id')
  {
    // Return getInHashArray result as a CSV
    $list = $this->getInHashArray($columnName);
    
    return Tools::CSV($list);
  }
  
  /**
   * Gets a hash of IDs as an array
   * 
   *  NB: Resets the current query.
   *      The object will be left in the state after the query.
   *
   * @param string $columnName Optionally the column to hash.  Default 'id'.
   * @return array
   */
  public function getInHashArray($columnName = 'id')
  {
    if(!$this->result)
    {
      // No result to work on
      return false;
    }
    
    // Start building the IN list
    $list = array();
    
    // Build the list
    while($row = $this->next())
    {
      $list[] = $row->{$columnName};
    }
    
    // Reset resource
    $this->rewind();
    
    return $list;
  }
   
  /**
   * Add an order condition
   * @param string $column The column to order by.
   * @param string $mode The ordering mode.
   * @return Database|boolean True on success, False on failure
   */
  public function order($column, $mode)
  {
    if(!$this->query)
    {
      return false;
    }
    
    $mode = strtoupper($mode);
    
    // Check mode
    if($mode != 'DESC' && $mode != 'ASC')
    {
      // Do nothing silently.
      return $this;
    }
    
    // Make column safe
    $column = $this->san($column);
    
    $this->query .= "ORDER BY {$column} {$mode} ";
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Limit a query to a specified range
   * @param integer $start The start position
   * @param integer $end Optional end position
   * @return Database|boolean  False on failure
   */
  public function limit($start, $end = '')
  {
    if(!$this->query)
    {
      return false;
    }
    
    $this->query .= 'LIMIT ';
    $start = intval($start);
  
    if(empty($end))
    {
      $this->query .= $start . ' '; 
    }
    else
    {
      $end = intval($end);
      $this->query .=  $start . ', ' . $end . ' ';
    } 
    
    // Return a ref. to this object for chainability
    return $this;
  }
  
  /**
   * Execute the pre-prepared statement
   * @param boolean $maintainQuery Optionally keep the query buffered after executuion.
   * @return Database|boolean
   */
  public function execute($maintainQuery = false)
  {
    try
    {
      if($this->query != '')
      {
        // Final check for ending in semicolon
        if(substr($this->query, -1) != ';')
        {
          $this->query .= ';';
        }
        
        // Execute!
        $this->result = mysql_query($this->query, $this->mysql);
        
        // Increase query count
        $this->parent->queries ++;
        
        // Failed?
        if(!$this->result)
        {
          throw new Exception('Query failed');
        }
      
        // Get the result stats
        // Some types of query don't produce enumerable results, so try to count only 
        if($this->result !== true && $this->result !== false)
        {
          $this->count = mysql_num_rows($this->result);
        }
        
        // Insert ID
        $this->insertId = mysql_insert_id($this->mysql);  // Legacy / X11
        $this->insertID = mysql_insert_id($this->mysql);  
        
        // Affected
        $this->affected = mysql_affected_rows($this->mysql);
        
        // Obtain query info
        $this->getQueryInfo();
        
        // Clear query
        if(!$maintainQuery)
        {
          $this->query = '';
        }
      }
    }
    catch(Exception $exception)
    {
      // Log failure
      $this->trace();
    
      throw new Exception('MySQL query failed: ' . $this->query . "\n" . mysql_error());
      return false;
    }
    
    // Reset the pointer
    $this->pointer = 0;
    
    return $this;
  }
  
  /**
   * Reset the current query
   * @return Database
   */
  public function reset()
  {
    // Reset the pointer
    $this->pointer = 0;
    
    // Reset query
    $this->query = '';
    
    // Reset results
    $this->result = null;
    
    // Reset Stats and Counters
    $this->count = 0;
    $this->insertID = 0;
    $this->insertId = 0; // Legacy / X11
    $this->affected = 0;
    
    return $this;
  }
  
  /**
   * Retrieve the next row as an object or optionally an array
   * @param boolean $asArray Optionally return arrays instead of stdClass objects.
   * @return object|array
   */
  public function next($asArray = false)
  {
    // Advance pointer
    $this->pointer ++;
    $row = ($asArray ? mysql_fetch_array($this->result) : mysql_fetch_object($this->result));
    
    return $row;
  }
  
  /**
   * Is this the final row?
   * @return boolean
   */
  public function last()
  {
    return ($this->pointer == $this->count);
  }
  
  /**
   * Is this the first row?
   * @return boolean
   */
  public function first()
  {
    return ($this->pointer == 0);
  }
  
  /**
   * Update the info property of the object to reflect the state of the last query.
   * @return boolean
   */
  private function getQueryInfo()
  {
    // Get the Info string
    $infoString = mysql_info($this->mysql);
    
    // Clear collection
    $this->info = array();
    
    // Parse
    $matches = array();
    preg_match_all('/([\w]+[\s]?[\w]+): ([0-9]+)[\s]?/', $infoString, $matches, PREG_SET_ORDER);
    
    // Load into the info property
    foreach($matches as $match)
    {
      $keyName = strtolower(str_replace(' ', '_', $match[1]));
      $this->info[$keyName] = $match[2];
    }
  
    return true;
  }
   
  /**
   * Finish all transactions and close the database connection.
   * @return Database
   */
  public function close()
  {
    // Free memory
    $this->result = null;
    
    // Close all resources
    mysql_close($this->mysql);
    $this->mysql = null;
    
    return $this;
  }
  
  /**
   * Rewind the position to the 0th row.
   * @return boolean True on success, False on failure
   */
  public function rewind()
  {
    try 
    {
      mysql_data_seek($this->result, 0);
      return true;
    }
    catch(Exception $e)
    {
      // Unseekable row.
      return false;
    }
    
    return false;
  }
  
  /**
   * Trace a query
   * @return boolean True on success, False on failure
   */
  public function trace()
  {
    $this->parent->log('Query failed', $this->query . '     ' . $this->error());
  }
  
  /**
   * Convert the current result set to an associative array
   *
   * NB: The current pointer position will be reset after a call to this method.
   *     This can cause a large memory leak when used on big data sets.
   *     As such, the method is limited to 1000 row result sets.
   *
   * @param string $key Optionally the field name to use as the key, default `id`
   * @return array
   */
  public function assoc($key = 'id')
  {
    if(!$this->result || $this->count > 1000)
    {
      // No result to work on or data set too large
      return false;
    }
  
    // Compile in to associative array
    $resultArray = array();
    
    while($current = $this->next(true))
    {
      foreach($current as $currentKey => $currentValue)
      {
        // Copy in to result set
        $resultArray[$current[$key]][$currentKey] = $currentValue;
      }
    }
    
    // Rewind
    $this->rewind();
        
    return $resultArray;
  }   
  
  /**
   * Wrapper to retrieve error
   * @return string
   */
  public function error()
  {
    return mysql_error($this->mysql);
  }
  
  /**
   * Sanitize an input string
   * @param mixed $input The input string
   * @return string
   */
  public function san($input = '')
  {
    if(is_array($input))
    {
      // Replace each entity with a safe version
      $queryText = $input[0];
      
      for($index = 1; $index < count($input); $index ++)
      {
        $safePart = $this->san($input[$index]);
        $queryText = str_replace('{' . $index . '}', $safePart, $queryText);
      }
      
      return $queryText;
    }
    else
    {
      return mysql_real_escape_string($input);
    }
  }
}

?>