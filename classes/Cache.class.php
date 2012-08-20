<?php
/** 
 * Cache Class
 * Provides one-shot levelled ID->Value caching to reduce required queries.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Cache extends Base
{
  /** 
   * Get a row from the cache if possible
   * Returns an associative array representing a row from the database
   *   or false if not found.
   * @param string $tableName The name of the virtual table to access
   * @param integer $ID The ID of the row to access
   * @return array|boolean
   */
  public function getRow($tableName, $ID)
  { 
    // Try to find the row in the cache
    $cacheResult = $this->parent->memcache->get('com.b2bfront.cache.' . 
                   $tableName . '.' . $ID);    
                   
    // Increment global cache hit count
    if($cacheResult)
    {
      $this->parent->cacheHits ++;
    }
    
    return ($cacheResult ? $cacheResult : false);
  }
  
  /**
   * Cache a row
   * If the row already exists, it will be updated automatically.
   * @param string $tableName The name of the virtual table to store to
   * @param integer $ID The ID of the row to save
   * @param mixed $data The row to store, as either an Array or StdClass object
   * @param integer $TTL Optionally the TTL of the cached row, default 7200.
   * @return boolean
   */
  public function addRow($tableName, $ID, $data, $TTL = 7200)
  {
    $this->parent->memcache->set('com.b2bfront.cache.' . $tableName .
      '.' . $ID, $data, false, intval($TTL));
      
    return true;
  }
  
  /** 
   * Mark a row as stale
   * @param string $tableName The name of the virtual table to remove from
   * @param integer $ID The ID of the row to mark
   * @return boolean
   */
  public function removeRow($tableName, $ID)
  {
    $this->parent->memcache->delete('com.b2bfront.cache.' . $tableName .
      '.' . $ID);
      
    return true;
  }
  
  /**
   * Advances an existing (or creates a new) Cache Level ID.
   * Returns the current value for the Cache Level ID.
   * @param string $cacheLevelID The existing or new cache level ID.
   * @return integer
   */
  public function advanceLevel($cacheLevelID)
  {
    // Find the current level
    $cacheResult = $this->parent->memcache->get('com.b2bfront.cache-levels.' . $cacheLevelID);
    
    if($cacheResult === false)
    {
      // Create one
      $this->parent->memcache->set('com.b2bfront.cache-levels.' . $cacheLevelID,
        0, 0, 172800); 
        
      $this->parent->log('Cache', 'Created Cache Level: ' . $cacheLevelID);
        
      return 0;
    }
    
    // Advance it
    $this->parent->memcache->delete('com.b2bfront.cache-levels.' . $cacheLevelID);
    $this->parent->memcache->set('com.b2bfront.cache-levels.' . $cacheLevelID,
      $cacheResult + 1, false, 172800); 
      
    $this->parent->log('Cache', 'Advanced Cache Level: ' . $cacheLevelID . 
      ' - now ' . ($cacheResult + 1));
      
    return $cacheResult + 1; 
  }
  
  /** 
   * Gets the current value of a Cache Level
   * Creates the Cache Level if it does not already exist.
   * @param string $cacheLevelID The existing or new cache level ID.
   * @return integer
   */
  public function getLevel($cacheLevelID)
  {
    // Find the current level
    $cacheResult = $this->parent->memcache->get('com.b2bfront.cache-levels.' . $cacheLevelID);
    
    if(!$cacheResult)
    {
      // Create one
      $this->parent->memcache->set('com.b2bfront.cache-levels.' . $cacheLevelID,
        0, false, 172800); 
        
      return 0;
    }
    
    return intval($cacheResult);
  }
  
  /**
   * Build a cache level prestring from a Cache Level ID
   * @param string $cacheLevelID The cache level ID.
   * @return string
   */
  public function getCacheLevelString($cacheLevelID)
  {
    // Get the cache level value
    $cacheLevelValue = $this->getLevel($cacheLevelID);
  
    // Build string and return
    return 'CL-' . $cacheLevelID . ':' . $cacheLevelValue .  ':' ;
  }
  
  /** 
   * Cache a generic value with a generic unrestricted keyname.
   * Keynames should conform to the reverse-hostname style.
   * @param string $key The key to store.
   * @param string $value The value to store.
   * @param integer $TTL Optionally the TTL of the cached pair, default 7200.
   * @param string $cacheLevel Optionally a cache level ID to use, default none (false).
   * @return boolean
   */
  public function addValue($key, $value, $TTL = 7200, $cacheLevel = false)
  {
    $cachePath = ($cacheLevel ? $this->getCacheLevelString($cacheLevel) : '') . 
      'com.b2bfront.cache.' . $key;
      
    $this->parent->memcache->set($cachePath, $value, 0, $TTL);
      
    return true;
  }
  
  /**
   * Retrieve a generic value with a generic unrestricted keyname from the cache.
   * Keynames should conform to the reverse-hostname style.
   * Returns false if the key cannot be found.
   * @param string $key The key to search for
   * @param string $cacheLevel Optionally a cache level ID to use, default none (false).
   * @return mixed|boolean
   */
  public function getValue($key, $cacheLevel = false)
  {
    // Try to find the value in the cache
    $cachePath = ($cacheLevel ? $this->getCacheLevelString($cacheLevel) : '') . 
      'com.b2bfront.cache.' . $key;
    $cacheResult = $this->parent->memcache->get($cachePath);    
                  
    // Increment global cache hit count
    if($cacheResult !== false)
    {
      $this->parent->cacheHits ++;
    }
    
    return ($cacheResult ? $cacheResult : false);
  }
  
  /**
   * Remove a generic value from the cache.
   * Should not be used to remove rows added with the addRow() method.
   * @param string $key The key to search for and remove if found
   * @param string $cacheLevel Optionally a cache level ID to use, default none (false).
   * @return boolean
   */
  public function removeValue($key, $cacheLevel = false)
  {
    $cachePath = ($cacheLevel ? $this->getCacheLevelString($cacheLevel) : '') . 
      'com.b2bfront.cache.' . $key;  

    $this->parent->memcache->delete($cachePath);
      
    return true;
  } 
}
?>