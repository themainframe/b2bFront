<?php
/** 
 * Business Object Model
 * Category Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMCategory extends BOMObject
{
  /**
   * Load a category from the database with an ID
   * @param integer $categoryID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMCategory|boolean
   */
  public function __construct($categoryID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$categoryID)
    {
      return;
    }
    
    // Load the category
    $categoryRow = $parent->db->getRow('bf_categories', $categoryID);
    
    // Missing
    if(!$categoryRow)
    {
      $this->parent->log('BOM', 'No such category: ' . $categoryID);
      return false;
    }
    
    // Set each value
    foreach($categoryRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
  
  /**
   * Load subcategories
   * @return array
   */
  protected function loadSubcategories()
  {
    // Collect subcategories
    $subcategories = $this->parent->db->query();
    $subcategories->select('*', 'bf_subcategories')
                  ->where('`category_id` = \'{1}\'', $this->id)
                  ->order('name', 'asc')
                  ->execute();
                 
    // Build BOMSubcategories with them and populate an array
    $categorySubcategories = array();
    
    while($subcategory = $subcategories->next())
    {
      $categorySubcategories[$subcategory->id] = 
        BOMSubcategory::initWithRow($subcategory, & $this->parent);
    }
    
    return $categorySubcategories;
  }
  
  /**
   * Load items that are not subcategorised
   * @return array
   */
  protected function loadLooseItems()
  {
    // Collect items
    $items = $this->parent->db->query();
    $items->select('*', 'bf_items')
          ->where('`category_id` = \'{1}\' AND `subcategory_id` = \'-1\' AND `parent_item_id` = -1',
                  $this->id)
          ->order('sku', 'asc')
          ->execute();
             
    // Build BOMItems with them and populate an array
    $this->looseItems = array();
    
    while($item = $items->next())
    {
      $this->looseItems[$item->id] = 
        BOMItem::initWithRow($item, & $this->parent);
    }
    
    return $this->looseItems;
  }
  
  /**
   * Load items
   * @return array
   */
  protected function loadItems()
  {
    // Collect items
    $items = $this->parent->db->query();
    $items->select('*', 'bf_items')
          ->where('`category_id` = \'{1}\'',
                  $this->id)
          ->order('sku', 'asc')
          ->execute();
             
    // Build BOMItems with them and populate an array
    $this->items = array();
    
    while($item = $items->next())
    {
      $this->items[$item->id] = 
        BOMItem::initWithRow($item, & $this->parent);
    }
    
    return $this->items;
  }
}
?>