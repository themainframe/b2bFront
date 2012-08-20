<?php
/** 
 * Business Object Model
 * Brand Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMBrand extends BOMObject
{
  /**
   * Load an brand from the database with an ID
   * @param integer $brandID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMItem|boolean
   */
  public function __construct($brandID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$brandID)
    {
      return;
    }
    
    // Load the brand
    $brandRow = $parent->db->getRow('bf_brands', $brandID);
    
    // Missing
    if(!$brandRow)
    {
      $this->parent->log('BOM', 'No such brand: ' . $brandID);
      return false;
    }
    
    // Set each value
    foreach($brandRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
}
?>