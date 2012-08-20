<?php
/** 
 * Configurable Class
 * Provides configurable functionality for the inheriting class.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Configurable extends Base
{
  /**
   * Configuration settings for this object
   * @var array
   */
  protected $configuration = array();
  
  /**
   * Set a configuration option value.
   * If the option is already set it will be overwritten.
   * @param string $name The name of the option
   * @param mixed $value Optionally the value of the option. True by default.
   * @return boolean
   */
  public function setOption($name, $value = true)
  {
    $this->configuration[$name] = $value;
    return true;
  }
  
  /**
   * Set multiple options.
   * $array is an associative array of key => value.
   * @param array $array An associative array of options to set.
   * @return boolean
   */
  public function setOptions($array)
  {
    foreach($array as $key => $value)
    {
      $this->setOption($key, $value);
    }
    
    return true;
  }
  
  /**
   * Set the default configuration on this object
   * @return boolean
   */
  public function defaults()
  {
    return true;
  }
  
  /**
   * Get a configuration option value.
   * Returns false if the option is not set.
   * @param string $name The name of the option.
   * @return mixed
   */
  public function getOption($name)
  {
    if(isset($this->configuration[$name]))
    {
      return $this->configuration[$name];
    }
    else
    {
      return false;
    }
  }
}
?>