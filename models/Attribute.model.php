<?php
/** 
 * Model: Attribute
 * Provides a listing of matched attributes for a classification, attribute ID and value
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Attribute extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Get the item information
    $item = $this->db->getRow('bf_items', $this->parent->inInteger('item_id'));
    
    // Not found?
    if(!$item)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'The item context for this search is invalid.');
      
      return false;
    }
    
    // Show item ID and SKU
    $this->addValue('itemSKU', $item->sku);
    $this->addValue('itemID', $item->id);

    // Get the classification information
    $classification = $this->db->getRow('bf_classifications', $this->parent->inInteger('id'));
    
    // Not found?
    if(!$classification)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'That classification of item does not exist.');
      
      return false;
    }
    
    // Show class name
    $this->addValue('classificationName', $classification->name);
    
    // Try to find the attribute name, make sure it is part of this class too
    $attribute = $this->db->getRow('bf_classification_attributes', $this->parent->inInteger('attribute_id'));
    
    // Valid?
    if(!$attribute || $attribute->classification_id != $classification->id)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'That classification of item is not valid.');
      
      return false;
    }
    
    // Show attribute name
    $this->addValue('attributeName', $attribute->name);
    
    // Find the value
    $value = $this->db->getRow('bf_item_attribute_applications', $this->parent->inInteger('value'));
    
    // Check it
    if(!$value || $value->classification_attribute_id != $attribute->id)
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', 'That attribution of item is not valid.');
      
      return false;
    }
    
    // Show value
    $this->addValue('attributeValue', $value->value);
    
    // Find other items that have this value set
    $items = $this->parent->db->query();
    $items->select('item_id', 'bf_item_attribute_applications')
          ->where('`value` = \'{1}\' AND `classification_attribute_id` = \'{2}\'', 
            $value->value, $value->classification_attribute_id)
          ->execute();
    
    // Get in hash
    $hash = $items->getInHash('item_id');
  
    // Find items
    $query = $this->db->query();
    $query->select('0 AS is_parent, parent_item_id, id, sku, name, trade_price, pro_net_price, pro_net_qty, category_id,' . 
                 'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
          ->where('`id` IN(' . $hash . ') AND `visible` = 1');
                  
    // Precache cart values
    $this->parent->cart->prefetch();
      
    // Construct table
    $dataView = new DataTable('items', $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('showBottomPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns($this->defaultColumns); 
    
    // Add the table to the view template
    $this->addValue('table', $dataView->render());  
    
    return true;
  }
}  
?>