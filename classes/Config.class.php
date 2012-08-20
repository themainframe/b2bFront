<?php
/** 
 * Config Class
 * Provides access to configuration information at runtime.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Config extends Base
{
  /**
   * The current path
   * @var string
   */
  private $path = 'com.';
  
  /**
   * The path to the configuration cache file
   * The location should be private
   * @var string
   */
  public $configCachePath = '/opt/b2bfront/config/cache.ini';
  
  /**
   * An associative array of configuration data
   * @var array
   */
  private $config = array();
  
  /**
   * Init (Psuedo-constructor) method
   * @return boolean
   */
  public function init()
  {
    // Load values into memory
    $this->load();
    
    // Verify that the configuration system is operable
    if(!Tools::exists($this->configCachePath) ||
       !is_writable($this->configCachePath))
    {
      // Failed
      throw new Exception('The configuration cache path is not writable.');
    }
    
    return true;
  }
  
  /**
   * Set the current path
   * @param string $path The new path
   * @return boolean
   */
  public function setPath($path)
  {
    $this->path = $path;
    return true;
  }
  
  /**
   * Retrieve a config value in the current path
   * @param string $configName The name of the value to retrieve
   * @param boolean $absolutePath Optionally use $configName as an absolute path. Default false.
   * @return mixed
   */
  public function get($configName, $absolutePath = false)
  {
    // Modify the name if this is not an absolute path
    if(!$absolutePath)
    {
      $configName = $this->path . '.' . $configName;
    }
  
    // Check for the value in the config array
    if(!array_key_exists($configName, $this->config))
    {
      // Perhaps a sync is required
      $this->sync();
    }
    
    // Check now
    if(!array_key_exists($configName, $this->config))
    {
      // Log this
      $this->parent->log('Missing configuration value: ' . $configName);
      
      return false;
    }
    
    $value = $this->config[$configName];

    // Boolean?
    if($value === 'true' || $value === '1')
    {
      $value = true;
    }
    
    if($value === 'false')
    {
      $value = false;
    }    

    return $value;
  }
  
  /**
   * Get a collection of configuration values in a given domain
   * Eg: com.b2bfront.site
   * NB. $configDomain is an Absolute config path.
   * @param string $configDomain The configuration domain to search
   * @return array An array of Config Name => Config Value
   */
  public function getDomain($configDomain = 'com.')
  {
    // Find domain keys
    $this->db->select('*', 'bf_config')
             ->where("`name` LIKE '{1}%'", $configDomain)
             ->execute();
             
    // Build an array
    $resultArray = array();
    
    // Load each
    while($config = $this->db->next())
    {
      $resultArray[$config->name] = $config->value;
    }
    
    return $resultArray;
  }

  /**
   * Set a config value in the current path
   * @param string $configName The name of the value to set, will be created if required.
   * @param string $configValue Optionally the value to assign
   * @param string $configDescription Optionally a description of the config key/value pair.
   * @return boolean True on success, False on failure
   */
  public function set($configName, $configValue = '', $configDescription = '')
  {
    if(!$this->db)
    {
      return false;
    }
    
    // Set the value
    $this->db->select('*', 'bf_config')
             ->where("`name` = '{1}'", $configName)
             ->limit(1)
             ->execute();
    
    if($this->db->count == 0)
    {
      // Create config value in DB
      
      $this->db->insert('bf_config', array(
        'name' => $configName,
        'value' => $configValue,
        'description' => $configDescription
      ));
      
      // Run query
      $this->db->execute();
    }         
    else
    {
      // Update config value in DB
      
      $this->db->update('bf_config',
                         array(
                           'value' => $configValue
                         )
                       )
               ->where("`name` = '{1}'", $configName)
               ->limit(1)
               ->execute();
    }
    
    // Update the value in memory
    $this->config[$configName] = $configValue;
    
    // Cause a sync
    $this->sync();
    
    return true;    
  }
  
  /**
   * Synchronize configuration data in the MySQL database with the config cache.
   * Should be called every time a modification is made to the configuration in MySQL.
   * @return boolean
   */
  public function sync()
  {
    // Check writable
    if(!is_writable($this->configCachePath))
    {
      return false;
    }
                       
    // Rewrite config file
    // Truncate to 0b first
    $handle = fopen($this->configCachePath, 'w');
    
    // Is the file handle OK?
    if(!$handle)
    {
      return false;
    }
    
    // Read all config data
    $this->db->select('*', 'bf_config')
             ->order('name', 'asc')
             ->execute();
    
    // Write intro
    fwrite($handle, '; b2bFront configuration cache file' . "\n");
    fwrite($handle, '; Last configuration cache commit: ' . Tools::longDate() . "\n\n");
    
    // Begin write
    while($configItem = $this->db->next())
    {
      fwrite($handle, $configItem->name . ' = "' . $configItem->value . '"' . "\n");
    }
    
    // Done, close the file
    fclose($handle);
    
    // Now load the values back into memory
    $this->load();
    
    return true;
  }
  
  /**
   * Copy configuration values from the cache file into memory
   * @return boolean
   */
  public function load()
  {
    // Load the config cache into memory
    if(!file_exists($this->configCachePath) || !is_readable($this->configCachePath))
    {
      // No cache file found, remake it
      $this->sync();
    }
    
    // Does the file exist now?
    if(!file_exists($this->configCachePath) || !is_readable($this->configCachePath))
    {
      // Critical failure to load the configuration data
      throw new Exception('Unable to load the configuration information.' . 
                          ' Check that ' . $this->configCachePath . ' is writable.');
    }
    
    // Load all data
    $this->config = parse_ini_file($this->configCachePath);
    
    if(!is_array($this->config))
    {
      // Failed to load configuration data, try a sync.
      $this->sync();
      
      // Try to load again
      $this->config = parse_ini_file($this->configCachePath);
    }
    
    // Check the configuration again
    if(!is_array($this->config))
    {
      // Critical failure to load the configuration data
      throw new Exception('Unable to load the configuration information.' . 
                          ' Check that ' . $this->configCachePath . ' is writable.');
    }
    
    return true;
  }
  
}
?>