<?php
/** 
 * Business Object Model
 * Base Class
 *
 * Abstract Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
abstract class BOMObject extends Base
{
  /**
   * The internal collection of attributes and properties belonging to the item 
   * that this BOMObject represents.
   * @var array
   */
  public $attributes = array();
  
  /**
   * Create a new BOMObject instance.
   * @param BFClass* $parent The parent object.
   * @return BOMObject
   */
  public function __construct(& $parent)
  {
    // Parent required for access
    if(!$parent)
    {
      throw new Exception('Unable to create BOM objects without BFClass instance.');
    }
  
    // Superclass constructor (Base)
    parent::__construct($parent, $parent->db);
  }
  
  /**
   * Alternative constructor
   * Generate a BOM Object with a preloaded row (StdClass object)
   * @param StdClass $row The row to load the object from.
   * @param BFClass $parent The parent object.
   * @return BOMItem|boolean
   */
  public static function initWithRow($row, $parent)
  {
    // Create new object with late static binding
    $newObject = new static(false, $parent);

    if($row)
    {
      foreach($row as $key => $value)
      {
        $newObject->{$key} = $value;
      }
    }

    return $newObject;
  }
  
  /**
   * Automatic accessor generation
   * Generates getter methods for BOMObject attributes.
   * @param string $name The name of the property to retrieve.
   * @return mixed|boolean
   */
  public final function __get($name)
  {  
    // Find the attribute
    if(array_key_exists($name, $this->attributes))
    {
      return $this->attributes[$name];
    }
    else
    { 
      // Attempt to load the attribute
      if(method_exists($this, 'load' . ucfirst($name)))
      {
        // Return newly-generated attribute
        return $this->{'load' . $name}();
      }
      else
      {
        // Attribute failed to load
        $this->parent->log('BOM', 'Cannot access attribute: ' . $name);
        return false;
      }
    }
  }
  
  /**
   * Automatic accessor generation
   * Generates setter methods for BOMObject attributes.
   * @param string $name The name of the property to assign to.
   * @param mixed $value The value to assign to the property.
   * @return boolean
   */
  public final function __set($name, $value)
  {  
    // Find the attribute and assign it
    $this->attributes[$name] = $value;
    
    return true;
  }
}
?>