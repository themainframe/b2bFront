<?php
/** 
 * Base Class
 * Abstract class
 *
 * Provides access to the main BFClass object from any class.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
abstract class Base
{
  /**
   * A ref to the parent BFClass object.
   * @var BFClass*
   */
  public $parent = null;
  
  /**
   * A shortcut to the Database object
   * Should be defined when instanciated by the class instanciatee.
   * @var Database*
   */
  public $db = null;
  
  /**
   * Create the object and bind it's parent to it.
   * @param BFClass* $parent Optionally A referance to the parent object.
   * @param Database* $db Optionally A referance to the parent object's database object.
   * @return object
   */
  public function __construct(& $parent = null, $database = null)
  {
    // Set parent object accessor
    $this->parent = $parent;
    
    // Set database shorthand accessor
    if($database)
    {
      $this->db = $database;
    }
    else
    {
      $this->db = & $parent->db;
    }
    
    // If the init method exists, call it now
    // This permits a "constructor" method to exist without replacing this one.
    if(method_exists($this, 'init'))
    {
      $this->init();
    }
  }
  
  /**
   * Magic Getter/Setter generation proxy.
   * Generates get_* and set_* methods on the fly.
   * Non-compatible calls will cause exceptions.
   * @param string $name The name of the called method.
   * @param array $arguments Any arguments passed to the called method.
   * @return mixed
   */
  public function __call($name, $arguments)
  {
    // Get or Set call?
    $callType = substr($name, 0, 4);
    $propertyName = substr($name, 4);
    
    if($callType == 'get_')
    {
      // Get the property value
      return $this->{$propertyName};
    }
    
    if($callType == 'set_')
    {
      if(isset($arguments[0]))
      {
        // Set the property value
        $this->{$propertyName} = $arguments[0];
      }
      
      return true;
    }
    
    // Undefined method
    throw new Exception('Call to undefined method: ' . $name);
    
    return false;
  }
}
?>
