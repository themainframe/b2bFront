<?php
/** 
 * Pricer Class
 * Provides services for pricing and price adjustment
 * and (sub)total calculation.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Pricer extends Base
{
  /** 
   * Get a matrix multiplier value for a given band and category
   * Abstracts the caching process
   * @param integer $bandID The band ID to use.
   * @param integer $categoryID The category ID to use.
   * @return float
   */
  private function getMatrixValue($bandID, $categoryID)
  {
    // Attempt to find in memcache
    $cacheKey = 'matrix-' . $bandID . '-' . $categoryID;
    $cacheAttempt = 
      $this->parent->cache->getValue($cacheKey, 'matrix');
  
    if($cacheAttempt)
    {
      // Success
      return floatval($cacheAttempt);
    }
    
    // Find value in DB
    $valueSearch = $this->parent->db->query();
    $valueSearch->select('value', 'bf_matrix')
                ->where('`band_id` = \'{1}\' AND `category_id` = \'{2}\'',
                        $bandID, $categoryID)
                ->limit(1)
                ->execute();
                
    // Found?
    if($valueSearch->count != 1)
    {
      // Default value
      return 1.0;
    }
    
    // Find row
    $valueRow = $valueSearch->next();
    
    // Cache the multiplier value
    $this->parent->cache->addValue($cacheKey, floatval($valueRow->value), 7200, 'matrix');
    
    return floatval($valueRow->value);
  }

  /**
   * Calculate a subtotal for an item
   * @param stdClass $itemRow The item row as an object with public properties.
   * @param integer $quantity Optionally the quantity of the item to use. Default 1.
   * @return float|boolean
   */
  public function subtotal($itemRow, $quantity = 1)
  {
    // Find price of each * quantity
    $eachPrice = $this->each($itemRow, $quantity);
    return Tools::price($eachPrice * $quantity);
  }
  
  /**
   * Find the price for each of the specified items
   * @param stdClass $itemRow The item row as an object with public properties.
   * @param integer $quantity Optionally the quantity of the item to use. Default 1.
   * @return float|boolean
   */
  public function each($itemRow, $quantity = 1)
  {
    // Override?
    $override = $this->getOverride($itemRow);
    
    if($override !== -1)
    {
      return Tools::price($override['pro_net_price']);
    }
  
    // Wholesale-capable user?
    if($this->parent->security->can('can_wholesale') && $itemRow->wholesale_price != 0.00)
    {
      // Always wholesale price override regardless of PNQ
      return Tools::price($itemRow->wholesale_price);
    }
  
    // Trade price?
    if($quantity >= $itemRow->pro_net_qty || $this->parent->security->can('can_pro_rate'))
    {        
      // Get this user's band information
      $userBandID = $this->parent->security->attributes['band_id'];
      
      // Load matrix row
      $matrixValue = $this->getMatrixValue($userBandID, $itemRow->category_id);
    
      // Overridden pro-net price?
      if($override !== -1)
      {
        return Tools::price($override['pro_net_price']);
      }
      else
      {
        // Valid?
        if(!$userBandID || $userBandID == -1)
        {
          // Potentially aborted session or no band - Trade
          return Tools::price($itemRow->trade_price);
        }
        
        // Calculate
        return Tools::price($itemRow->pro_net_price * $matrixValue);
      }
    }

    // Overridden trade price?
    if($override !== -1)
    {
      return Tools::price($override['trade_price']);
    }

    // Simple trade price.
    return Tools::price($itemRow->trade_price);
  }
  
  /**
   * Find the "my price" for a specified item.
   * This is the "best case" price for the current dealer, regardless of quantity.
   * @param stdClass $itemRow The item row as an object with public properties.
   * @return float|boolean
   */
  public function myPrice($itemRow)
  {
    // Override?
    $override = $this->getOverride($itemRow);
  
    // Calculate
    if($override !== -1)
    {
      return Tools::price($override['pro_net_price']);
    }
  
    // Wholesale-capable user?
    if($this->parent->security->can('can_wholesale') && $itemRow->wholesale_price != 0.00)
    {
      // Always wholesale price override regardless of PNQ
      return Tools::price($itemRow->wholesale_price);
    }
    
    // Pro Net Price
    // Get this user's band information
    $userBandID = $this->parent->security->attributes['band_id'];
    
    // Valid?
    if(!$userBandID || $userBandID == -1)
    {
      // Potentially aborted session or no band - Trade
      return Tools::price($itemRow->trade_price);
    }
      
    // Load matrix row
    $matrixValue = $this->getMatrixValue($userBandID, $itemRow->category_id);

    // Calculate
    if($override !== -1)
    {
      return Tools::price($override['pro_net_price']);
    }
    else
    {    
      return Tools::price($itemRow->pro_net_price * $matrixValue);
    }
  }
  
  /**
   * Find any overrides for the specified item row.
   * Returns a mesh of prices as a key=>value array if found, or false if not.
   * @param stdClass $itemRow The item row as an object with public properties.
   * @return array|boolean
   */
  public function getOverride($itemRow)
  {
    // Cached?
    $cacheKey = 'override-' . $this->parent->security->UID . '-' . 
      $itemRow->id;
      
    // Search cache
    $cacheAttempt = $this->parent->cache->getValue($cacheKey, 'user-' .
      $this->parent->security->UID);
  
    if($cacheAttempt !== false)
    {
      // Success
      return $cacheAttempt;
    }
    
    // Find value in DB
    $overrideSearch = $this->parent->db->query();
    $overrideSearch->select('trade_price, pro_net_price', 'bf_user_prices')
                   ->where('`user_id` = \'{1}\' AND `item_id` = \'{2}\'',
                           $this->parent->security->UID, $itemRow->id)
                   ->limit(1)
                   ->execute();
                
    // Found?
    if($overrideSearch->count != 1)
    {
      // Cache default value
      $this->parent->cache->addValue($cacheKey, -1, 7200, 'user-' .
        $this->parent->security->UID);
             
      // Return 
      return -1;
    }
    
    // Find row
    $overrideRow = $overrideSearch->next();
    
    $result = array(
      'trade_price' => floatval($overrideRow->trade_price),
      'pro_net_price' => floatval($overrideRow->pro_net_price)
    );
    
    // Cache
    $this->parent->cache->addValue($cacheKey, $result, 7200, 'user-' .
      $this->parent->security->UID);

    // Return
    return $result;
  }
}
?>