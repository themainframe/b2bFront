<?php
/** 
 * Business Object Model
 * Subsubcategory Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMSubcategory extends BOMObject
{
  /**
   * Load a subcategory from the database with an ID
   * @param integer $subcategoryID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMCategory|boolean
   */
  public function __construct($subcategoryID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$subcategoryID)
    {
      return;
    }
    
    // Load the subcategory
    $subcategoryRow = $parent->db->getRow('bf_subcategories', $subcategoryID);
    
    // Missing
    if(!$subcategoryRow)
    {
      $this->parent->log('BOM', 'No such subcategory: ' . $subcategoryID);
      return false;
    }
    
    // Set each value
    foreach($subcategoryRow as $key => $value)
    {
      $this->{$key} = $value;
    }
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
          ->where('`subcategory_id` = \'{1}\' AND `parent_item_id` = -1',
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
    
    // Search for parented items
    $items = $this->parent->db->query();
    $items->select('*', 'bf_items')
          ->where('`parent_item_id` IN (SELECT `id` FROM `bf_parent_items` WHERE `subcategory_id` = \'{1}\')',
                  $this->id)
          ->order('sku', 'asc')
          ->execute();

  $this->parent->log('ITEMS:', $items->count);

    // Add items to the list
    while($item = $items->next())
    {
      $this->items[$item->id] = 
        BOMItem::initWithRow($item, & $this->parent);
    }

    return $this->items;
  }
}
?>