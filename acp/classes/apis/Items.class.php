<?php
/**
 * Items
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Items extends API
{
  /**
   * Add an item to the inventory
   * @param string $SKU The unique SKU of the item.
   * @param string $name The name of the item.
   * @param double $tradePrice The trade price.
   * @param double $proNetPrice The Pro Net Price.
   * @param integer $proNetQty The Pro Net Quantity.
   * @param double $wholesalePrice The wholesale price.
   * @param double $rrpPrice The RRP / MSRP.
   * @param integer $stockFree The free stock units.
   * @param integer $stockHeld The held stock units.
   * @param integer $stockDate Optionally The estimated arrival date for stock.
   * @param double $costPrice Optionally The Cost price.
   * @param string $barcode Optionally The barcode (EAN or UPC).
   * @param string $description Optionally a description.
   * @param integer $classificationID Optionally The classification ID.
   * @param integer $categoryID Optionally The category ID.
   * @param integer $subcategoryID Optionally The subcategory ID.
   * @param integer $brandID Optionally The brand ID.
   * @param string $keywords Optionally A CSV of keywords.
   * @param integer $parentID Optionally an ID of a parent item.
   * @return boolean|integer
   */
  public function add($SKU, $name, $tradePrice, $proNetPrice, $proNetQty, $wholesalePrice, $rrpPrice,
                      $stockFree, $stockHeld, $stockDate = 0, $costPrice = 0.0, $barcode = '', $description = '',
                      $classificationID = -1, $categoryID = -1, $subcategoryID = -1, $brandID = -1, $keywords = '',
                      $parentID = -1)
  {
    $itemInsert = $this->db->query();
    $itemInsert->insert('bf_items',
                        array(
                          'sku' => $SKU,
                          'name' => $name,
                          'trade_price' => $tradePrice,
                          'pro_net_price' => $proNetPrice,
                          'pro_net_qty' => $proNetQty,
                          'wholesale_price' => $wholesalePrice,
                          'rrp_price' => $rrpPrice,
                          'cost_price' => $costPrice,
                          'stock_free' => $stockFree,
                          'stock_held' => $stockHeld,
                          'stock_date' => $stockDate,
                          'description' => $description,
                          'barcode' => $barcode,
                          'classification_id' => $classificationID,
                          'category_id' => $categoryID,
                          'subcategory_id' => $subcategoryID,
                          'brand_id' => $brandID,
                          'parent_item_id' => $parentID
                        )
                       )
               ->execute();
    
    return $itemInsert->insertID;
  }
  
  /**
   * Remove an Item.
   * @param string $itemID The ID of the item to remove.
   * @return boolean
   */
  public function remove($itemID)
  {
    // Find images and remove them
    $images = $this->db->query();
    $images->select('*', 'bf_item_images')
           ->where('item_id = \'{1}\'', $itemID)
           ->execute();
    
    // Remove each
    while($image = $images->next())
    {
      $this->db->delete('bf_images')
               ->where('`id` = \'{1}\'', $image->image_id)
               ->limit(1)
               ->execute();
    }
    
    // Remove the item
    $this->db->delete('bf_items')
             ->where("`id` = '{1}'", $itemID)
             ->limit(1)
             ->execute();
             
    // Remove item tag assignments
    $this->db->delete('bf_item_tag_applications')
             ->where("`item_id` = '{1}'", $itemID)
             ->execute();

    // Remove item attribute assignments
    $this->db->delete('bf_item_attribute_applications')
             ->where("`item_id` = '{1}'", $itemID)
             ->execute();
             
    // Update order lines to deleted item
    $this->db->update('bf_order_lines', array(
                       'item_id' => '-1' 
                     ))
             ->where("`item_id` = '{1}'", $itemID)
             ->execute();
             
    // Remove variation data
    $this->db->delete('bf_parent_item_variation_data')
             ->where("`item_id` = '{1}'", $itemID)
             ->execute();
               
    // Remove image links
    $this->clearImages($itemID);
               
    return true;
  }
  
  /**
   * Modify an item and save the changes to the DB
   * @param integer $itemID The ID of the item to modify.
   * @param string $SKU The unique SKU of the item.
   * @param string $name The name of the item.
   * @param double $tradePrice The trade price.
   * @param double $proNetPrice The Pro Net Price.
   * @param integer $proNetQty The Pro Net Quantity.
   * @param double $wholesalePrice The wholesale price.
   * @param double $rrpPrice The RRP / MSRP.
   * @param integer $stockFree The free stock units.
   * @param integer $stockHeld The held stock units.
   * @param integer $stockDate Optionally The estimated arrival date for stock.
   * @param double $costPrice Optionally The Cost price.
   * @param string $barcode Optionally The barcode (EAN or UPC).
   * @param string $description Optionally a description.
   * @param integer $classificationID Optionally The classification ID.
   * @param integer $categoryID Optionally The category ID.
   * @param integer $subcategoryID Optionally The subcategory ID.
   * @param integer $brandID Optionally The brand ID.
   * @param string $keywords Optionally The keywords string.
   * @return boolean
   */
  public function modify($itemID, $SKU, $name, $tradePrice, $proNetPrice, $proNetQty, $wholesalePrice, $rrpPrice,
                         $stockFree, $stockHeld, $stockDate = 0, $costPrice = 0.0, $barcode = '', $description = '',
                         $classificationID = -1, $categoryID = -1, $subcategoryID = -1, $brandID = -1, $keywords = '')
  {
    $itemModify = $this->db->query();
    $result = $itemModify->update('bf_items',
                                  array_merge(
                                    array(
                                      'sku' => $SKU,
                                      'name' => $name,
                                      'trade_price' => $tradePrice,
                                      'pro_net_price' => $proNetPrice,
                                      'pro_net_qty' => $proNetQty,
                                      'wholesale_price' => $wholesalePrice,
                                      'rrp_price' => $rrpPrice,
                                      'cost_price' => $costPrice,
                                      'stock_free' => $stockFree,
                                      'stock_held' => $stockHeld,
                                      'description' => $description,
                                      'barcode' => $barcode,
                                      'classification_id' => $classificationID,
                                      'category_id' => $categoryID,
                                      'subcategory_id' => $subcategoryID,
                                      'brand_id' => $brandID,
                                      'keywords' => $keywords
                                    ),
                                    
                                    // Optionally, the due date if changed
                                    
                                    $stockDate != '' ?
                                      
                                      $stockDate == ' ' ? 
                                      
                                        array(
                                          'stock_date' => ''             // Stock due date intentionally cleared.
                                        )
                                      
                                      : 
                                      
                                        array(
                                          'stock_date' => $stockDate    // Stock due date updated.
                                        )
                                      
                                    : array()                           // Stock due date not changed.
                                    
                                    
                                   )
                                 )
                         ->where('`id` = \'{1}\'', $itemID)
                         ->limit(1)
                         ->execute();
   
    return $result;
  }
   
  /**
   * Count the number of items in the inventory.
   * @return integer
   */
  public function count()
  {
    $items = $this->db->query();
    $items->select('1', 'bf_items')
          ->execute();
    
    return $items->count;
  }
   
  /**
   * Count the number of unclassified items in the Inventory.
   * @return integer
   */
  public function countUnclassified()
  {
    $unclassified = $this->db->query();
    $unclassified->select('1', 'bf_items')
                 ->where('classification_id = -1')
                 ->execute();
    
    return $unclassified->count;
  }
   
  /**
   * Count the number of uncategorised items in the Inventory.
   * @return integer
   */
  public function countUncategorised()
  {
    $uncategorised = $this->db->query();
    $uncategorised->select('1', 'bf_items')
                  ->where('category_id = -1')
                  ->execute();
    
    return $uncategorised->count;
  }
  
  /** 
   * Remove all images from an item
   * @param integer $itemID The ID of the item
   * @return boolean
   */
  public function clearImages($itemID)
  {
    $this->db->delete('bf_item_images')
             ->where("`item_id` = '{1}'", $itemID)
             ->execute();
    
    return true;
  }
  
  /**
   * Attach the specified image ID to this item
   * @param integer $itemID The ID of the item
   * @param integer $imageID The ID of the image
   * @param integer $priority Optionally the priority of the assignment
   * @return boolean
   */
  public function attachImage($itemID, $imageID, $priority = 1)
  {
    $this->db->insert('bf_item_images', array(
                       'priority' => $priority,
                       'item_id' => $itemID,
                       'image_id' => $imageID
                     ))
             ->execute();
             
    return true;
  }
  
  /**
   * Add a **PARENT** item to the inventory
   * @param string $SKU The unique Virtaul SKU of the parent item.
   * @param string $name The name of the parent item.
   * @param double $tradePrice The parent item's trade price.
   * @param double $proNetPrice The parent item's Pro Net Price.
   * @param integer $proNetQty The parent item's Pro Net Quantity.
   * @param double $wholesalePrice The parent item's wholesale price.
   * @param double $rrpPrice The parent item's RRP / MSRP.
   * @param double $costPrice Optionally The parent item's Cost price.
   * @param string $description Optionally a description for the parent item.
   * @param integer $classificationID Optionally The parent item's classification ID.
   * @param integer $categoryID Optionally The parent item's category ID.
   * @param integer $subcategoryID Optionally The parent item's subcategory ID.
   * @param integer $brandID Optionally The parent item's brand ID.
   * @return boolean|integer
   */
  public function addParent($SKU, $name, $tradePrice, $proNetPrice, $proNetQty, $wholesalePrice, $rrpPrice,
                            $costPrice = 0.0, $description = '', $classificationID = -1, $categoryID = -1, 
                            $subcategoryID = -1, $brandID = -1)
  {
    $parentItemInsert = $this->db->query();
    $parentItemInsert->insert('bf_parent_items',
                              array(
                                'sku' => $SKU,
                                'name' => $name,
                                'trade_price' => $tradePrice,
                                'pro_net_price' => $proNetPrice,
                                'pro_net_qty' => $proNetQty,
                                'wholesale_price' => $wholesalePrice,
                                'rrp_price' => $rrpPrice,
                                'cost_price' => $costPrice,
                                'description' => $description,
                                'classification_id' => $classificationID,
                                'category_id' => $categoryID,
                                'subcategory_id' => $subcategoryID,
                                'brand_id' => $brandID
                              )
                             )
                     ->execute();
    
    return $parentItemInsert->insertID;
  }
  
  /**
   * Remove a **PARENT** Item.
   * @param string $itemID The ID of the parent item to remove.
   * @return boolean
   */
  public function removeParent($parentItemID)
  {
    // Remove the item
    $this->db->delete('bf_parent_items')
             ->where("`id` = '{1}'", $parentItemID)
             ->limit(1)
             ->execute();
             
    // Load variations
    $variations = $this->db->select('*', 'bf_parent_item_variations')
                           ->where('`parent_item_id` = \'{1}\'', $parentItemID)
                           ->execute();
                           
    // Remove variation data for each
    $variationIDs = $variations->getInHash();
    $this->db->delete('bf_parent_item_variation_data')
             ->whereInHash($variationIDs)
             ->execute();
             
    // Remove parent item variations
    $this->db->delete('bf_parent_item_variations')
             ->where("`parent_item_id` = '{1}'", $parentItemID)
             ->execute();

    // Make child items in to standard (orphan) items
    $this->db->update('bf_items', array(
                       'parent_item_id' => '-1' 
                     ))
             ->where("`parent_item_id` = '{1}'", $parentItemID)
             ->execute();
             
    // Remove parent item attribute assignments
    $this->db->delete('bf_parent_item_attribute_applications')
             ->where("`parent_item_id` = '{1}'", $parentItemID)
             ->execute();

    return true;
  }
}
?>