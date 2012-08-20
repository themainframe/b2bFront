<?php
/** 
 * Business Object Model
 * Parent Item Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMParentItem extends BOMObject
{
  /**
   * Load a parent item from the database with an ID
   * @param integer $parentItemID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMParentItem|boolean
   */
  public function __construct($parentItemID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$parentItemID)
    {
      return;
    }
    
    // Load the parent item
    $parentItemRow = $parent->db->getRow('bf_parent_items', $parentItemID);
    
    // Missing
    if(!$parentItemRow)
    {
      $this->parent->log('BOM', 'No such parent item: ' . $itemID);
      return false;
    }
    
    // Set each value
    foreach($parentItemRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
  
  /**
   * Load child items
   * @return array
   */
  protected function loadChildren()
  {
    // Collect child items
    $children = $this->parent->db->query();
    $children->select('*', 'bf_items')
             ->where('`parent_item_id` = \'{1}\'',
                     $this->id)
             ->execute();
             
    // Build BOMItems with them and populate an array
    $this->children = array();
    
    while($child = $children->next())
    {
      $this->children[$child->id] = 
        BOMItem::initWithRow($child, & $this->parent);
    }
    
    return $this->children;
  }
  
  /**
   * Load images
   * Automatically loads a collection of images from the child items
   * @return array
   */
  protected function loadImages()
  {
    // Build image collection
    $images = array();
  
    foreach($this->children as $child)
    {
      foreach($child->images as $image)
      {
        $images[] = $image;
      }
    }
   
    return $images;
  }
  
  /**
   * Load the brand
   * @return BOMBrand
   */
  protected function loadBrand()
  {
    return new BOMBrand($this->brand_id, & $this->parent);
  }
  
  /**
   * Load all properties as an associative array
   * @return array
   */
  protected function loadProperties()
  {
    // Load classification attributes for this parent item
    $classificationAttributes = $this->parent->db->query();
    $classificationAttributes->select('*', 'bf_classification_attributes')
                             ->where('`classification_id` = \'{1}\'',
                                $this->classification_id)
                             ->execute();
                             
    // Build a collection of properties
    $properties = array();
    
    while($classificationAttribute = $classificationAttributes->next())
    {
      $properties[$classificationAttribute->id] = 
        $classificationAttribute->name;
    }
    
    // Load classification attribute applications for this parent item
    $classAttribApplications = $this->parent->db->query();
    $classAttribApplications->select('*', 'bf_parent_item_attribute_applications')
                            ->where('`parent_item_id` = \'{1}\'', $this->id)
                            ->execute();
                          
    // Build an associative array
    $attributes = array();
    
    while($classAttribApplication = $classAttribApplications->next())
    {
      $attributes[$properties[$classAttribApplication->classification_attribute_id]] = 
        $classAttribApplication->value;
    }
    
    return $attributes;
  }
}
?>