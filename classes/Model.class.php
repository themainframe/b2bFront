<?php
/** 
 * Model Class
 * All model files extend this class.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Model extends Base
{
  /**
   * The values this model has generated.
   * @var array
   */
  public $values = array();

  /**
   * Cause the model to do any processing required.
   * This method will usually be overridden by child classes
   * @return boolean;
   */
  public function execute()
  {
    // No more parent classes.
    return true;
  }
  
  /**
   * Add values to the model output
   * @param array $values The values to add.
   * @return boolean
   */
  public function add($values)
  {
    // Merge the values.
    $this->values = array_merge($this->values, $values);
    
    return true;
  }
  
  /**
   * Update or add a single value to the values collection
   * @param string $key The key of the value
   * @param mixed $value Optionally the value, default empty string.
   * @return boolean
   */
  public function addValue($key, $value = '')
  {
    // Merge the value with the collection
    $this->values[$key] = $value;
    
    return true;
  }
  
  /**
   * Retrieve the processed values
   * @return array
   */
  public function getValues()
  {
    return $this->values;
  }
}
?>