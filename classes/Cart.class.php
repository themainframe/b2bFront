<?php
/** 
 * Shopping Cart Class
 * Provides services for manipulating shopping cart contents.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Cart extends Base
{
  /**
   * An associative array of item ID => quantity
   * Produced by the prefetch() method.
   * @var array
   */
  private $basketContents = array();
  
  /** 
   * Instructs the cart object to prefectch quantities
   * Preferable to query-thrashing during list view rendering
   * @return boolean
   */
  public function prefetch()
  {
    // Reset contents
    $this->clearPrefetch();
    
    $this->parent->db->select('`item_id`, `quantity`', 'bf_user_cart_items')
                     ->where('`user_id` = \'{1}\'',
                             intval($this->parent->security->UID))
                     ->execute();
                     
    // Save each in the cache
    while($basketItem = $this->parent->db->next())
    {
      $this->basketContents[$basketItem->item_id] = $basketItem->quantity;
    }

    return true;
  }
  
  /**
   * Clear the prefetched cache
   * @return boolean
   */
  private function clearPrefetch()
  {
    $this->basketContents = array();
    
    return true;
  }
  
  /**
   * Get the number of a specific item ID in the current basket
   * @param integer $itemID The ID of the item to look for.
   * @param boolean $trustCache Trust that the cache contains up-to-date zero values.
   * @return integer
   */
  public function get($itemID, $trustCache = false)
  {
    // Is the item cached?
    if(array_key_exists($itemID, $this->basketContents))
    {
      return $this->basketContents[$itemID];
    }
    else
    {
      if($trustCache)
      {
        return 0;
      }
    }
    
    // Retrieve from data
    $result = $this->parent->db->query();
    $result->select('*', 'bf_user_cart_items')
           ->where('user_id = \'{1}\' AND item_id = \'{2}\'', 
              $this->parent->security->UID, intval($itemID))
           ->limit(1)
           ->execute();
        
    $resultRow = $result->next();
      
    
    // Not found?  
    if(!$resultRow)
    {
      return 0;
    }
    
    // Return value
    return intval($resultRow->quantity);
  }

  /**
   * Count the number of items in the user basket
   * NB. This is the number of *lines* not the sum of the quantities.
   * @return integer
   */
  public function count()
  {
    $this->parent->db->select('`id`', 'bf_user_cart_items')
                     ->where('`user_id` = \'{1}\'',
                             intval($this->parent->security->UID))
                     ->execute();
                     
    return intval($this->parent->db->count);
  }

  /** 
   * Remove an item with the specified ID from the cart
   * @param integer $itemID The ID of the item to remove.
   * @return boolean
   */
  public function remove($itemID)
  {
    $this->parent->db->delete('bf_user_cart_items')
                     ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'',
                             intval($itemID), intval($this->parent->security->UID))
                     ->execute();
    
    // Empty cache               
    $this->clearPrefetch();
                     
    return true;
  }
  
  /** 
   * Clear the shopping cart.
   * @return boolean
   */
  public function clear()
  {
    $this->parent->db->delete('bf_user_cart_items')
                     ->where('`user_id` = \'{1}\'', intval($this->parent->security->UID))
                     ->execute();
               
    // Empty cache       
    $this->clearPrefetch();
                     
    return true;
  }
  
  /**
   * Add an item to the cart.
   * If the item already exists, it's quantity will be updated.
   * @param integer $itemID The ID of the item to add.
   * @param integer $quantity Optionally the number of items to add, default 1.
   * @return boolean
   */
  public function add($itemID, $quantity = 1)
  {
    // Empty cache       
    $this->clearPrefetch();
  
    // Set to 0? Delete.
    if($quantity == 0)
    {
      $this->parent->db->delete('bf_user_cart_items')
                       ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'',
                               intval($itemID), intval($this->parent->security->UID))
                       ->execute();
                       
      return true;
    }
    
    // Search basket
    $search = $this->parent->db->query();
    $search->select('*', 'bf_user_cart_items')
           ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'', intval($itemID), intval($this->parent->security->UID))
           ->limit(1)
           ->execute();
    
    // In basket already?
    if($search->count == 1)
    {
      // Try to update and change the quantity
      $this->parent->db->update('bf_user_cart_items', array(
                           'quantity' => intval($quantity)
                         ))
                       ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'',
                               intval($itemID), intval($this->parent->security->UID))
                       ->execute();
    }
    else
    {
      // Try to update and change the quantity
      $this->parent->db->insert('bf_user_cart_items', array(
                           'item_id' => intval($itemID),
                           'user_id' => intval($this->parent->security->UID),
                           'quantity' => intval($quantity)
                         ))
                       ->execute();
    }
    
    return true;
  }
}
?>